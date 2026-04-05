<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\TaskService;
use App\Support\Request;

final class TaskController extends BaseController
{
    public function __construct(private readonly TaskService $tasks)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $bucket = (string)$request->input('bucket', 'all');

        $this->view('tasks/index', [
            'title' => 'Tarefas e Follow-up',
            'tasks' => $this->tasks->list($tenantId, $bucket),
            'bucket' => $bucket,
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $this->tasks->create($tenantId, $userId, [
            'title' => (string)$request->input('title', ''),
            'description' => (string)$request->input('description', ''),
            'assigned_user_id' => (string)$request->input('assigned_user_id', ''),
            'due_at' => (string)$request->input('due_at', ''),
            'status' => (string)$request->input('status', 'pending'),
            'contact_id' => (string)$request->input('contact_id', ''),
            'conversation_id' => (string)$request->input('conversation_id', ''),
        ]);

        $this->redirect('/tasks');
    }

    public function update(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $taskId = (int)$request->input('id');

        $this->tasks->update($tenantId, $userId, $taskId, [
            'title' => (string)$request->input('title', ''),
            'description' => (string)$request->input('description', ''),
            'assigned_user_id' => (string)$request->input('assigned_user_id', ''),
            'due_at' => (string)$request->input('due_at', ''),
            'status' => (string)$request->input('status', 'pending'),
        ]);

        $this->redirect('/tasks');
    }

    public function changeStatus(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $taskId = (int)$request->input('id');
        $status = (string)$request->input('status', 'pending');

        $this->tasks->changeStatus($tenantId, $userId, $taskId, $status);
        $this->redirect('/tasks');
    }
}
