<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Listing::with(['category', 'images', 'user:id,name'])
            ->where('status', 'active');

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Filter by condition
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['created_at', 'price', 'views_count'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $listings = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'condition' => 'required|in:new,used,excellent,good,fair',
            'location' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'is_negotiable' => 'boolean',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = Listing::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency ?? 'SYP',
            'condition' => $request->condition,
            'location' => $request->location,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'is_negotiable' => $request->is_negotiable ?? true,
            'expires_at' => now()->addDays(30), // Default 30 days expiry
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('listings', $filename, 'public');

                Image::create([
                    'listing_id' => $listing->id,
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $listing->load(['category', 'images', 'user:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Listing created successfully',
            'data' => $listing
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        // Increment views count
        $listing->increment('views_count');

        $listing->load(['category', 'images', 'user:id,name,phone']);

        return response()->json([
            'success' => true,
            'data' => $listing
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Listing $listing)
    {
        // Check if user owns the listing
        if ($listing->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'condition' => 'required|in:new,used,excellent,good,fair',
            'location' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'is_negotiable' => 'boolean',
            'status' => 'nullable|in:active,sold,expired,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing->update($request->all());
        $listing->load(['category', 'images', 'user:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Listing updated successfully',
            'data' => $listing
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Listing $listing)
    {
        // Check if user owns the listing
        if ($listing->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete associated images from storage
        foreach ($listing->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $listing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted successfully'
        ]);
    }

    /**
     * Get user's listings
     */
    public function myListings(Request $request)
    {
        $listings = Listing::with(['category', 'images'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }
}

