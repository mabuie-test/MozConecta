<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\InboxService;
use App\Support\Request;

final class InboxController extends BaseController
{
    public function __construct(private readonly InboxService $inbox)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $filters = [
            'search' => (string)$request->input('search', ''),
            'status' => (string)$request->input('status', ''),
            'assigned_user_id' => (string)$request->input('assigned_user_id', ''),
        ];

        $this->view('inbox/index', [
            'title' => 'Inbox Multiatendente',
            'conversations' => $this->inbox->listConversations($tenantId, $filters),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $conversationId = (int)$request->input('id');
        $details = $this->inbox->conversationDetails($tenantId, $conversationId);

        $this->view('inbox/show', [
            'title' => 'Timeline da Conversa',
            'details' => $details,
        ]);
    }

    public function send(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $conversationId = (int)$request->input('conversation_id');
        $body = (string)$request->input('body', '');

        $this->inbox->sendMessage($tenantId, $actorUserId, $conversationId, $body);
        $this->redirect('/inbox/show?id=' . $conversationId);
    }

    public function addNote(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $conversationId = (int)$request->input('conversation_id');
        $note = (string)$request->input('note', '');

        $this->inbox->addInternalNote($tenantId, $actorUserId, $conversationId, $note);
        $this->redirect('/inbox/show?id=' . $conversationId);
    }

    public function assign(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $conversationId = (int)$request->input('conversation_id');
        $assignedUserId = (int)$request->input('assigned_user_id');

        $this->inbox->assignConversation($tenantId, $actorUserId, $conversationId, $assignedUserId);
        $this->redirect('/inbox/show?id=' . $conversationId);
    }

    public function takeover(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $conversationId = (int)$request->input('conversation_id');

        $this->inbox->takeover($tenantId, $actorUserId, $conversationId);
        $this->redirect('/inbox/show?id=' . $conversationId);
    }

    public function changeStatus(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $conversationId = (int)$request->input('conversation_id');
        $status = (string)$request->input('status', 'open');

        $this->inbox->changeStatus($tenantId, $actorUserId, $conversationId, $status);
        $this->redirect('/inbox/show?id=' . $conversationId);
    }
}
