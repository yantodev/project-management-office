<?php

namespace App\Http\Controllers\Projects;

use App\Entities\Users\User;
use Illuminate\Http\Request;
use App\Entities\Projects\Issue;
use App\Entities\Projects\Project;
use App\Http\Controllers\Controller;
use App\Entities\Projects\IssueStatus;

class IssueController extends Controller
{
    public function index(Project $project)
    {
        $issues = $project->issues()->with(['pic', 'creator'])->get();

        return view('projects.issues', compact('project', 'issues'));
    }

    public function create(Project $project)
    {
        $users = User::pluck('name', 'id');

        return view('projects.issues.create', compact('project', 'users'));
    }

    public function store(Request $request, Project $project)
    {
        $issueData = $request->validate([
            'title'  => 'required|max:60',
            'body'   => 'required|max:255',
            'pic_id' => 'nullable|exists:users,id',
        ]);
        Issue::create([
            'project_id' => $project->id,
            'creator_id' => auth()->id(),
            'title'      => $issueData['title'],
            'body'       => $issueData['body'],
            'pic_id'     => $issueData['pic_id'],
        ]);
        flash(__('issue.created'), 'success');

        return redirect()->route('projects.issues.index', $project);
    }

    public function show(Project $project, Issue $issue)
    {
        $statuses = IssueStatus::toArray();
        $users = User::pluck('name', 'id');

        return view('projects.issues.show', compact('project', 'issue', 'users', 'statuses'));
    }

    public function edit(Project $project, Issue $issue)
    {
        return view('projects.issues.edit', compact('project', 'issue'));
    }

    public function update(Request $request, Project $project, Issue $issue)
    {
        $issueData = $request->validate([
            'title' => 'required|max:60',
            'body'  => 'required|max:255',
        ]);
        $issue->title = $issueData['title'];
        $issue->body = $issueData['body'];
        $issue->save();

        flash(__('issue.updated'), 'success');

        return redirect()->route('projects.issues.show', [$project, $issue]);
    }

    public function destroy(Request $request, Project $project, Issue $issue)
    {
        $request->validate(['issue_id' => 'required']);

        if ($request->get('issue_id') == $issue->id && $issue->delete()) {
            flash(__('issue.deleted'), 'warning');

            return redirect()->route('projects.issues.index', $project);
        }
        flash(__('issue.undeleted'), 'danger');

        return back();
    }
}
