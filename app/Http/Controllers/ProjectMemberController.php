<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Traits\LogsUserActivity;

class ProjectMemberController extends Controller
{
    use LogsUserActivity;
    
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display project members management page.
     */
    public function index(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $users = User::all();
        $members = $project->members;

        return view('projects.members.index', compact('project', 'users', 'members'));
    }

    /**
     * Update project members.
     */
    public function update(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $request->validate([
            'members' => 'array',
            'members.*' => 'exists:users,id',
        ]);

        // Always ensure lead is a member
        $members = $request->members ?? [];
        if (!in_array($project->lead_id, $members)) {
            $members[] = $project->lead_id;
        }

        // Always ensure current user is a member if they're updating the project
        if (!in_array(Auth::id(), $members)) {
            $members[] = Auth::id();
        }

        // Sync project members
        $project->members()->sync($members);

        return redirect()->route('projects.members.index', $project)
            ->with('success', 'Project members updated successfully.');
    }

    /**
     * Display change project lead form.
     */
    public function editLead(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $members = $project->members;

        return view('projects.members.change_lead', compact('project', 'members'));
    }

    /**
     * Update project lead.
     */
    public function updateLead(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $request->validate([
            'lead_id' => 'required|exists:users,id',
        ]);

        // Ensure the new lead is a member of the project
        if (!$project->members->contains($request->lead_id)) {
            $project->members()->attach($request->lead_id);
        }

        // Update the project lead
        $project->update([
            'lead_id' => $request->lead_id,
        ]);

        return redirect()->route('projects.members.index', $project)
            ->with('success', 'Project lead updated successfully.');
    }

    /**
     * Invite a user to join the project by email.
     */
    public function invite(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $request->validate([
            'invite_email' => 'required|email',
        ]);

        // Check if user exists
        $user = User::where('email', $request->invite_email)->first();

        if ($user) {
            // User exists, add to project if not already a member
            if (!$project->members->contains($user->id)) {
                $project->members()->attach($user->id);
                return redirect()->route('projects.members.index', $project)
                    ->with('success', 'User added to project successfully.');
            } else {
                return redirect()->route('projects.members.index', $project)
                    ->with('info', 'User is already a member of this project.');
            }
        } else {
            // User doesn't exist, you might want to send an invitation email here
            
            // Generate invitation token
            $token = Str::random(32);
            
            // Store invitation in the database
            // Note: You'd need to create an invitations table first
            /*
            Invitation::create([
                'email' => $request->invite_email,
                'token' => $token,
                'project_id' => $project->id,
                'inviter_id' => Auth::id(),
                'expires_at' => now()->addDays(7),
            ]);
            
            // Send invitation email
            Mail::to($request->invite_email)->send(new ProjectInvitation($project, Auth::user(), $token));
            */
            
            // For now, just return with a message
            return redirect()->route('projects.members.index', $project)
                ->with('info', 'User does not exist. An invitation email would be sent in a production environment.');
        }
    }

    /**
     * Remove a specific member from the project.
     */
    public function removeMember(Request $request, Project $project, User $user)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        // Cannot remove project lead
        if ($user->id === $project->lead_id) {
            return redirect()->route('projects.members.index', $project)
                ->with('error', 'Cannot remove the project lead from the project.');
        }

        // Cannot remove yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('projects.members.index', $project)
                ->with('error', 'You cannot remove yourself from the project.');
        }

        // Remove the member
        $project->members()->detach($user->id);

        // Check if user has any tasks assigned in this project and unassign them
        $project->tasks()->where('assignee_id', $user->id)->update(['assignee_id' => null]);

        return redirect()->route('projects.members.index', $project)
            ->with('success', 'Member removed from project successfully.');
    }

    /**
     * Show member details and their project activities.
     */
    public function show(Project $project, User $member)
    {
        // Check if the user can view this project
        $this->authorize('view', $project);

        // Check if user is a member of the project
        if (!$project->members->contains($member->id)) {
            return redirect()->route('projects.members.index', $project)
                ->with('error', 'User is not a member of this project.');
        }

        // Get member's tasks in this project
        $assignedTasks = $project->tasks()->where('assignee_id', $member->id)->get();
        $reportedTasks = $project->tasks()->where('reporter_id', $member->id)->get();

        return view('projects.members.show', compact('project', 'member', 'assignedTasks', 'reportedTasks'));
    }

    /**
     * Accept an invitation to join a project.
     */
    public function acceptInvitation($token)
    {
        // Note: You'd need to create an invitations table first
        /*
        $invitation = Invitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Check if user is authenticated
        if (!Auth::check()) {
            // Store invitation token in session and redirect to login
            session(['invitation_token' => $token]);
            return redirect()->route('login')
                ->with('info', 'Please log in or register to join the project.');
        }

        // Add user to project
        $project = Project::findOrFail($invitation->project_id);
        $project->members()->attach(Auth::id());

        // Mark invitation as used
        $invitation->update([
            'accepted_at' => now(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'You have successfully joined the project.');
        */

        // For now, just redirect to home with a message
        return redirect()->route('dashboard')
            ->with('info', 'Invitation handling would be implemented in a production environment.');
    }

    /**
     * Add a single member directly from project page.
     */
    public function addMember(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if the user is already a member
        if ($project->members->contains($request->user_id)) {
            return redirect()->back()
                ->with('info', 'User is already a member of this project.');
        }

        // Add the user as a member
        $project->members()->attach($request->user_id);
        
        // Log activity
        $this->logUserActivity('Added user to project: ' . $project->name);

        return redirect()->back()
            ->with('success', 'Member added to project successfully.');
    }
}