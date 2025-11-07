<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ActivityLogController extends Controller
{
    public function index(Request $request){
        $query = ActivityLog::with('user');

        if ($request->has('user_id')){
            $query->where('id_user', $request->user_id);
        }

        if($request->has('dokumen_id')){
            $query->where('id_dokumen', $request->dokumen_id);
        }

        if($request->has('date_from')){
            $query->whereDate('waktu_aktivitas', '>=', $request->date_from);
        }

        if($request->has('date_from')){
            $query->whereDate('waktu_aktivitas', '<=', $request->date_to);
        }

        if($request->has('search')){
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('aktivitas', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('username', 'LIKE', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest()->paginate(10);

        return response()->json([
            'data' => [
                'activity_logs' => ActivityLogResource::collection($logs),
            ],
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'List of activity logs',
                'pagination' => [
                    'total' => $logs->total(),
                    'count' => $logs->count(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'total_pages' => $logs->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'id_dokumen' => 'nullable|uuid',
            'aktivitas' => 'required|string|max:255',
        ]);

        if ($validator->fails()){
            return response()->json([
                'meta' => [
                    'code' => 422,
                    'status' => 'error',
                    'message' => $validator->errors(),
                ]
                ], 422);
        }
        
        $log = ActivityLog::create([
            'id' => Str::uuid(),
            'id_user' => Auth::id(),
            'id_dokumen' => $request->id_dokumen,
            'aktivitas' => $request->aktivitas,
            'waktu_aktivitas' => now(),
        ]);
    
        return response()->json([
            'data' => new ActivityLogResource($log),
            'meta' => [
                'code' => 201,
                'status' => 'success',
                'message' => 'Activity log recorded successfully',
            ]
        ], 201);
    }

    public function show($id){
        $log = ActivityLog::with('user')->find($id);

        if(!$log){
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Activity log not found',
                ]
            ], 404);
        }

        return response()->json([
            'data' => new ActivityLogResource($log),
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Activity log fetched successfully',
            ]
            ]);
    }
}
