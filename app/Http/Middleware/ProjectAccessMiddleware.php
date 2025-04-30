<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProjectAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = $request->route('project');
        
        if ($projectId instanceof Project) {
            $projectId = $projectId->id;
        }
        
        // Check if user is a member of the project
        $isMember = Auth::user()->projects()->where('projects.id', $projectId)->exists();
        
        if (!$isMember) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized access to this project.'], 403);
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have access to this project.');
        }
        
        return $next($request);
    }
}