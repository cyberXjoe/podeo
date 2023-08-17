<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Episode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class EpisodeController extends Controller
{
    public function addEpisode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mp3_file' => 'required|file|mimes:mp3',
            'name' => 'required|string',
            'author' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'message' => $validator->errors(),
                    'status' => 422,
                    'type' => 'HttpResponseException',
                ],
            ], 422);
        }

        $episode = new Episode();

        if ($request->hasFile('mp3_file')) {
            $mp3File = $request->file('mp3_file');
            $fileName = time() . '_' . $mp3File->getClientOriginalName();
            $filePath = $mp3File->storeAs('episodes', $fileName, 'public');
            $episode->mp3_url = $filePath;
        }
        $episode->name = $request->name;
        $episode->author = $request->author;
        $episode->save();

        return response()->json(['message' => 'Episode added successfully', 'data' => $episode]);
    }

    public function flagAsPrivate(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);

        $episode->private = true;
        $episode->save();

        return response()->json(['message' => 'Episode flagged as private']);
    }

    public function getSignedUrl(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);

        if (!$episode->private) {
            return response()->json(['error' => 'Episode is not private']);
        }

        $signedUrl = $this->generateSignedUrl($id);
  
        return $signedUrl;
    }

    private function generateSignedUrl($id)
    {
        $signedUrl = URL::signedRoute('getEpisode', ['id' => $id]);
        $expiresAt = Carbon::now()->addHour();
        $signedUrl = URL::temporarySignedRoute('getEpisode', $expiresAt, ['id' => $id]);
        return $signedUrl;
    }

    public function getEpisode($id)
    {
        $episode = Episode::findOrFail($id);
        $publicUrl = asset('storage/' . $episode->mp3_url);

        $filePath = $episode->mp3_url;
        $path = Storage::get('public/'.$filePath);
        return $path;
    }

    public function streamEpisode(Request $request, $id)
    {
        $episode = Episode::findOrFail($id);
    
        // Check if the episode is private
        if ($episode->is_private && !$this->isAuthenticated($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
      
      
  
        // Generate a cache key based on the episode ID
        $cacheKey = "episode_stream_$id";

        // Check if the episode is cached
        if (Cache::has($cacheKey)) {
            // Return the cached episode content
            $content = Cache::get($cacheKey);
        } else {
            // Read the episode content from storage
            $filePath = $episode->mp3_url;
            $content = Storage::get('public/'.$filePath);
            // Cache the episode content for future requests
            Cache::put($cacheKey, $content, now()->addMinutes(30)); // Cache for 30 minutes
        }
        
        // Call the analytics service API endpoint (fire-and-forget)
        $x = $this->callAnalyticsService($episode->id);

        // Send the episode content as a stream with appropriate headers
        return response()->stream(
            fn () => print($content),
            200,
            [
                'Content-Type' => 'audio/mpeg',
                'Content-Length' => strlen($content),
                'Accept-Ranges' => 'bytes',
            ]
        );
    }

    private function isAuthenticated(Request $request)
    {
        return $request->user() || $request->hasValidSignature();
    }

    private function callAnalyticsService($episodeId)
    {
        $url = "http://nginx_two/api/callAnalytics";
        $response = Http::post($url, ['episode_id' => $episodeId]);
    	return $response->json();
    }
}