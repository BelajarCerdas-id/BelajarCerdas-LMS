{{-- ========================================================================
     RPPM - REGISTRASI GURU
     File:
     resources/views/features/lms/school-admin/registrasi-guru/tampilan/tamp-RPPM.blade.php

     FITUR:
     - DOCX existing menggunakan endpoint Laravel
     - Tidak fetch langsung dari /storage
     - Guru: upload, edit, autosave, delete
     - Selain Guru: view, komentar, reply, resolve, tandai revisi
     - Komentar berdasarkan selected text
     - Revisi berdasarkan selected text
     - Page dock
     - DOCX preview
     - Download
     - Fullscreen
     - Print
     - Zoom
======================================================================== --}}

@php
    $document = $document ?? null;
    $wordContent = $wordContent ?? null;
    $comments = collect($comments ?? []);

    $documentId = $documentId ?? ($document?->id ?? null);

    $routeRole = request()->route('role')
        ?? ($role ?? (auth()->check() ? auth()->user()->role : 'Guru'));

    $routeSchoolName = request()->route('schoolName')
        ?? ($schoolName ?? '');

    $routeSchoolId = request()->route('schoolId')
        ?? ($schoolId ?? '');

    $role = $routeRole;
    $schoolName = $routeSchoolName;
    $schoolId = $routeSchoolId;

    $currentUser = auth()->user();

    $isGuru = auth()->check()
        && strtolower(trim((string) $currentUser->role)) === 'guru';

    /*
     * ================================================================
     * ROUTE
     * ================================================================
     */

    $saveRoute = null;
    $downloadRoute = null;
    $deleteRoute = null;
    $fileRoute = null;

    $academicBrowseRoute = null;
    $academicSaveArchiveRoute = null;

    if (Route::has('lms.schoolAdmin.registrasiGuru.academicWord.browse')) {
        $academicBrowseRoute = route(
            'lms.schoolAdmin.registrasiGuru.academicWord.browse',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
                'type' => 'rppm',
            ]
        );
    }

    if ($documentId && Route::has('lms.schoolAdmin.registrasiGuru.academicWord.saveArchive')) {
        $academicSaveArchiveRoute = route(
            'lms.schoolAdmin.registrasiGuru.academicWord.saveArchive',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
                'type' => 'rppm',
                'documentId' => $documentId,
            ]
        );
    }

    if ($documentId) {
        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.save')) {
            $saveRoute = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.save',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                ]
            );
        }

        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.download')) {
            $downloadRoute = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.download',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                ]
            );
        }

        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.destroy')) {
            $deleteRoute = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.destroy',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                ]
            );
        }

        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.file')) {
            $fileRoute = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.file',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                ],
                false
            );
        }
    }

    $uploadRoute = null;

    if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.upload')) {
        $uploadRoute = route(
            'lms.schoolAdmin.registrasiGuru.rppm.word.upload',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
            ]
        );
    }

    $editRouteTemplate = null;

    if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.edit')) {
        $editRouteTemplate = route(
            'lms.schoolAdmin.registrasiGuru.rppm.word.edit',
            [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,
                'documentId' => '__DOCUMENT__',
            ]
        );
    }

    /*
     * ================================================================
     * COMMENT ROUTES
     * ================================================================
     */

    $commentStoreRoute = null;
    $commentReplyRouteTemplate = null;
    $commentResolveRouteTemplate = null;
    $commentDeleteRouteTemplate = null;

    if ($documentId) {
        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.comments.store')) {
            $commentStoreRoute = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.comments.store',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                ]
            );
        }

        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.comments.reply')) {
            $commentReplyRouteTemplate = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.comments.reply',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                    'commentId' => '__COMMENT__',
                ]
            );
        }

        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.comments.resolve')) {
            $commentResolveRouteTemplate = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.comments.resolve',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                    'commentId' => '__COMMENT__',
                ]
            );
        }

        if (Route::has('lms.schoolAdmin.registrasiGuru.rppm.word.comments.destroy')) {
            $commentDeleteRouteTemplate = route(
                'lms.schoolAdmin.registrasiGuru.rppm.word.comments.destroy',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'documentId' => $documentId,
                    'commentId' => '__COMMENT__',
                ]
            );
        }
    }

    /*
     * ================================================================
     * CONTENT
     * ================================================================
     */

    $existingContent =
        $wordContent->content
        ?? $wordContent->html_content
        ?? $wordContent->body
        ?? '';

    $existingFileName =
        $document?->original_filename
        ?? $document?->title
        ?? 'RPPM.docx';

    /*
     * ================================================================
     * COMMENT PAYLOAD
     * ================================================================
     */

    $rppmCommentsPayload = [];

    foreach ($comments as $comment) {
        $repliesPayload = [];

        foreach (collect($comment->replies ?? []) as $reply) {
            $replyUser = $reply->user ?? null;

            $repliesPayload[] = [
                'id' => $reply->id,
                'parent_id' => $reply->parent_id ?? null,
                'user_id' => $reply->user_id ?? null,
                'user_name' =>
                    $replyUser->name
                    ?? $replyUser->username
                    ?? 'Pengguna',
                'role' =>
                    $replyUser->role
                    ?? $reply->role
                    ?? '',
                'comment_text' =>
                    $reply->comment_text
                    ?? $reply->comment
                    ?? $reply->content
                    ?? $reply->body
                    ?? '',
                'created_at' =>
                    $reply->created_at?->format('d M Y H:i')
                    ?? null,
            ];
        }

        $commentUser = $comment->user ?? null;

        $rppmCommentsPayload[] = [
            'id' => $comment->id,
            'parent_id' => $comment->parent_id ?? null,
            'user_id' => $comment->user_id ?? null,
            'user_name' =>
                $commentUser->name
                ?? $commentUser->username
                ?? 'Pengguna',
            'role' =>
                $commentUser->role
                ?? $comment->role
                ?? '',
            'page_number' =>
                $comment->page_number ?? 1,
            'selected_text' =>
                $comment->selected_text ?? '',
            'comment_text' =>
                $comment->comment_text
                ?? $comment->comment
                ?? $comment->content
                ?? $comment->body
                ?? '',
            'anchor_key' =>
                $comment->anchor_key ?? null,
            'resolved' =>
                !empty($comment->resolved_at),
            'resolved_at' =>
                $comment->resolved_at?->toISOString()
                ?? null,
            'created_at' =>
                $comment->created_at?->format('d M Y H:i')
                ?? null,
            'replies' => $repliesPayload,
        ];
    }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>RPPM - Registrasi Guru</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.6/dist/docx-preview.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: Inter, Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
        }

        body {
            overflow-x: hidden;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        /* ============================================================
           APP
        ============================================================ */

        .rppm-app {
            --sidebar-width: 280px;

            margin-left: var(--sidebar-width);

            width: calc(
                100% - var(--sidebar-width)
            );

            min-height: 100vh;

            display: flex;
            flex-direction: column;
        }

        /* ============================================================
           HEADER
        ============================================================ */

        .rppm-header {
            position: sticky;
            top: 0;
            z-index: 1000;

            height: 68px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 20px;

            background: #ffffff;

            border-bottom: 1px solid #e2e8f0;
        }

        .rppm-header-left {
            min-width: 0;

            display: flex;
            align-items: center;
            gap: 12px;
        }

        .rppm-title-wrap {
            min-width: 0;
        }

        .rppm-title {
            margin: 0;

            font-size: 17px;
            font-weight: 800;

            color: #0f172a;
        }

        .rppm-subtitle {
            margin-top: 3px;

            font-size: 11px;
            color: #64748b;
        }

        .rppm-header-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .header-button {
            height: 38px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 0 12px;

            border: 1px solid #e2e8f0;
            border-radius: 9px;

            background: #ffffff;

            color: #334155;

            font-size: 12px;
            font-weight: 700;

            transition: .15s ease;
        }

        .header-button:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .header-button.primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .header-button.primary:hover {
            background: #1d4ed8;
        }

        .header-button.danger {
            color: #dc2626;
        }

        /* ============================================================
           TOOLBAR
        ============================================================ */

        .editor-toolbar {
            position: sticky;
            top: 68px;
            z-index: 900;

            min-height: 50px;

            display: flex;
            align-items: center;

            gap: 5px;

            padding: 6px 12px;

            background: #ffffff;

            border-bottom: 1px solid #e2e8f0;

            overflow-x: auto;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 3px;

            padding-right: 7px;
            margin-right: 3px;

            border-right: 1px solid #e2e8f0;
        }

        .toolbar-group:last-child {
            border-right: 0;
        }

        .command-button,
        .toolbar-select {
            height: 34px;

            border: 1px solid transparent;
            border-radius: 7px;

            background: transparent;

            color: #334155;

            font-size: 12px;
        }

        .command-button {
            min-width: 34px;
            padding: 0 8px;
        }

        .command-button:hover {
            background: #f1f5f9;
        }

        .toolbar-select {
            padding: 0 7px;
            border-color: #e2e8f0;
            background: #ffffff;
        }

        .readonly-mode .guru-only {
            display: none !important;
        }

        /* ============================================================
           FILE INFO
        ============================================================ */

        .file-info-bar {
            display: none;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            padding: 8px 14px;

            background: #f8fafc;

            border-bottom: 1px solid #e2e8f0;
        }

        .file-info-left {
            min-width: 0;

            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-icon {
            width: 31px;
            height: 31px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 7px;

            background: #dbeafe;
            color: #2563eb;
        }

        .file-meta {
            min-width: 0;
        }

        .file-name {
            max-width: 480px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            font-size: 12px;
            font-weight: 700;
        }

        .file-status {
            margin-top: 2px;

            font-size: 10px;
            color: #64748b;
        }

        .save-status {
            display: none;

            padding: 5px 9px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 700;
        }

        .save-status.show {
            display: inline-flex;
        }

        .save-status.success {
            background: #dcfce7;
            color: #166534;
        }

        .save-status.error {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ============================================================
           WORKSPACE
        ============================================================ */

        .rppm-workspace {
            flex: 1;
            min-height: 0;
            position: relative;
            display: grid;
            grid-template-columns: 164px minmax(0, 1fr);
            overflow: hidden;
        }

        /* Komentar menjadi layer/overlay. Tidak mengubah lebar dokumen. */
        .rppm-workspace .comments-panel {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 280px;
            max-width: min(280px, 82vw);
            z-index: 100;
            box-shadow: -6px 0 18px rgba(15, 23, 42, .10);
        }

        /* ============================================================
           PAGE DOCK
        ============================================================ */

        .page-dock {
            position: sticky;
            top: 118px;

            height: calc(100vh - 118px);

            padding: 12px 8px;

            overflow-y: auto;

            background: #f8fafc;

            border-right: 1px solid #e2e8f0;
        }

        .page-dock-header {
            padding: 4px 7px 9px;

            font-size: 10px;
            font-weight: 800;

            color: #64748b;

            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .page-tab {
            width: 100%;

            margin-bottom: 9px;
            padding: 6px;

            border: 1px solid #e2e8f0;
            border-radius: 8px;

            background: #ffffff;

            text-align: left;

            transition: .15s ease;
        }

        .page-tab:hover {
            border-color: #93c5fd;
        }

        .page-tab.active {
            border-color: #2563eb;
            box-shadow:
                0 0 0 1px #2563eb;
        }

        .page-number {
            display: block;

            margin-bottom: 5px;

            font-size: 9px;
            font-weight: 700;

            color: #64748b;
        }

        .page-thumb {
            width: 100%;

            overflow: hidden;

            border: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .page-thumb-inner {
            width: 100%;

            height: 145px;

            overflow: hidden;

            transform-origin: top left;
        }

        .page-thumb-inner .docx {
            transform: scale(.17);
            transform-origin: top left;

            box-shadow: none !important;

            margin: 0 !important;
        }

        /* ============================================================
           DOCUMENT WORKSPACE
        ============================================================ */

        .document-workspace {
            position: relative;

            min-width: 0;
            min-height: 0;

            overflow: auto;

            background:
                linear-gradient(
                    90deg,
                    #eef2f7 1px,
                    transparent 1px
                );

            background-size: 20px 20px;
        }

        .docx-stage {
            display: none;

            width: max-content;
            min-width: 100%;

            padding: 28px 24px 80px;
        }

        #docx-preview {
            width: max-content;
            min-width: 100%;
        }

        #docx-preview .docx-wrapper {
            padding: 0 !important;
            background: transparent !important;
        }

        #docx-preview .docx {
            margin: 0 auto 25px !important;

            box-shadow:
                0 7px 24px rgba(15, 23, 42, .12) !important;
        }

        /* ============================================================
           REVISION
        ============================================================ */

        .revision-mark {
            background: rgba(250, 204, 21, .42) !important;

            box-shadow:
                inset 0 -2px 0 #f59e0b;

            cursor: pointer;

            transition: background .15s ease;
        }

        .revision-mark:hover {
            background: rgba(250, 204, 21, .65) !important;
        }

        /* ============================================================
           EMPTY STATE
        ============================================================ */

        .empty-state {
            min-height: 70vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px;
        }

        .empty-card {
            width: min(560px, 100%);

            padding: 35px;

            border: 1px solid #e2e8f0;
            border-radius: 18px;

            background: #ffffff;

            text-align: center;

            box-shadow:
                0 8px 30px rgba(15, 23, 42, .05);
        }

        .empty-icon {
            width: 60px;
            height: 60px;

            margin: 0 auto 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 16px;

            background: #dbeafe;
            color: #2563eb;

            font-size: 25px;
        }

        .empty-card h2 {
            margin: 0;

            font-size: 20px;
        }

        .empty-card p {
            margin: 9px 0 20px;

            color: #64748b;

            font-size: 12px;
            line-height: 1.7;
        }

        .upload-dropzone {
            padding: 25px;

            border: 2px dashed #cbd5e1;
            border-radius: 14px;

            background: #f8fafc;

            transition: .15s ease;
        }

        .upload-dropzone:hover,
        .upload-dropzone.dragover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .upload-dropzone i {
            font-size: 25px;
            color: #2563eb;
        }

        .upload-dropzone strong {
            display: block;

            margin-top: 10px;

            font-size: 13px;
        }

        .upload-dropzone span {
            display: block;

            margin-top: 5px;

            font-size: 10px;
            color: #64748b;
        }

        .upload-main-button {
            margin-top: 15px;

            height: 38px;

            padding: 0 16px;

            border: 0;
            border-radius: 8px;

            background: #2563eb;
            color: #ffffff;

            font-size: 12px;
            font-weight: 700;
        }

        /* ============================================================
           COMMENTS PANEL
        ============================================================ */

        .comments-panel {
            min-width: 0;
            min-height: 0;

            display: none;
            flex-direction: column;

            overflow: hidden;

            background: #ffffff;

            border-left: 1px solid #e2e8f0;
        }

        .comments-open .comments-panel {
            display: flex;
        }

        .comments-header {
            height: 54px;
            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 13px;

            border-bottom: 1px solid #e2e8f0;
        }

        .comments-header-title {
            display: flex;
            align-items: center;
            gap: 7px;

            font-size: 12px;
            font-weight: 800;
        }

        .comments-count {
            min-width: 20px;
            height: 20px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border-radius: 999px;

            background: #dbeafe;
            color: #1d4ed8;

            font-size: 9px;
        }

        .close-comments-button {
            width: 29px;
            height: 29px;

            border: 0;
            border-radius: 7px;

            background: transparent;
            color: #64748b;
        }

        .close-comments-button:hover {
            background: #f1f5f9;
        }

        .comments-body {
            flex: 1 1 auto;

            min-height: 0;

            overflow-y: auto;
            overflow-x: hidden;

            padding: 12px;
        }

        .comment-empty {
            padding: 35px 14px;

            display: flex;
            flex-direction: column;
            align-items: center;

            text-align: center;

            color: #64748b;
        }

        .comment-empty i {
            margin-bottom: 10px;

            font-size: 25px;
            color: #94a3b8;
        }

        .comment-empty strong {
            color: #334155;
            font-size: 12px;
        }

        .comment-empty span {
            margin-top: 7px;

            font-size: 10px;
            line-height: 1.6;
        }

        .comment-card {
            position: relative;

            margin-bottom: 10px;
            padding: 11px;

            border: 1px solid #e2e8f0;
            border-radius: 10px;

            background: #ffffff;
        }

        .comment-card.is-revision {
            border-color: #fbbf24;

            background:
                linear-gradient(
                    180deg,
                    #fffbeb,
                    #ffffff
                );
        }

        .comment-card.resolved {
            opacity: .65;
        }

        .comment-revision-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            margin-bottom: 8px;
            padding: 4px 7px;

            border-radius: 999px;

            background: #fef3c7;
            color: #92400e;

            font-size: 9px;
            font-weight: 800;
        }

        .comment-user {
            display: flex;
            gap: 8px;
        }

        .comment-avatar {
            width: 28px;
            height: 28px;
            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #dbeafe;
            color: #1d4ed8;

            font-size: 10px;
            font-weight: 800;
        }

        .comment-user-info {
            min-width: 0;
        }

        .comment-user-info strong {
            display: block;

            font-size: 11px;
        }

        .comment-user-info span {
            display: block;

            margin-top: 2px;

            color: #64748b;

            font-size: 9px;
        }

        .comment-selected-text {
            margin-top: 9px;
            padding: 8px;

            border-left: 3px solid #f59e0b;

            background: #fffbeb;

            color: #92400e;

            font-size: 10px;
            line-height: 1.5;

            word-break: break-word;
        }

        .comment-content {
            margin-top: 9px;

            color: #334155;

            font-size: 11px;
            line-height: 1.6;

            white-space: pre-wrap;
            word-break: break-word;
        }

        .comment-actions-row {
            margin-top: 9px;

            display: flex;
            align-items: center;
            gap: 4px;
        }

        .comment-mini-button {
            height: 27px;

            padding: 0 7px;

            display: inline-flex;
            align-items: center;
            gap: 4px;

            border: 1px solid #e2e8f0;
            border-radius: 6px;

            background: #ffffff;
            color: #475569;

            font-size: 9px;
            font-weight: 700;
        }

        .comment-mini-button:hover {
            background: #f8fafc;
        }

        .comment-mini-button.danger {
            color: #dc2626;
        }

        .comment-replies {
            margin-top: 9px;
            padding-left: 10px;

            border-left: 2px solid #e2e8f0;
        }

        .comment-reply {
            margin-bottom: 7px;
        }

        .reply-user {
            font-size: 9px;
            font-weight: 800;
        }

        .reply-role {
            margin-left: 4px;

            color: #64748b;

            font-size: 8px;
        }

        .reply-text {
            margin-top: 3px;

            color: #475569;

            font-size: 10px;
            line-height: 1.5;
        }

        .reply-box {
            display: none;

            margin-top: 8px;
        }

        .reply-box.show {
            display: block;
        }

        .reply-input {
            width: 100%;
            min-height: 65px;

            resize: vertical;

            padding: 8px;

            border: 1px solid #cbd5e1;
            border-radius: 7px;

            outline: none;

            font-size: 10px;
        }

        .reply-input:focus {
            border-color: #60a5fa;
        }

        /* ============================================================
           COMMENT COMPOSER
        ============================================================ */

        .comment-composer {
            display: none;

            flex: 0 0 auto;

            position: sticky;
            bottom: 0;

            z-index: 30;

            padding: 12px;

            background: #ffffff;

            border-top: 1px solid #e5e7eb;

            box-shadow:
                0 -4px 15px rgba(15, 23, 42, .04);
        }

        .comment-composer.show {
            display: block;
        }

        .selection-preview {
            display: block;

            max-height: 65px;

            overflow: auto;

            margin-bottom: 7px;
            padding: 7px 8px;

            border-left: 3px solid #2563eb;

            background: #eff6ff;

            color: #1e3a8a;

            font-size: 9px;
            line-height: 1.5;
        }

        .comment-textarea {
            width: 100%;
            min-height: 75px;

            resize: vertical;

            padding: 9px;

            border: 1px solid #cbd5e1;
            border-radius: 8px;

            outline: none;

            font-size: 10px;
            line-height: 1.5;
        }

        .comment-textarea:focus {
            border-color: #60a5fa;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, .08);
        }

        .composer-actions {
            display: flex;
            justify-content: flex-end;

            gap: 5px;

            margin-top: 7px;
        }

        .composer-button {
            height: 30px;

            padding: 0 10px;

            border-radius: 7px;

            font-size: 9px;
            font-weight: 700;
        }

        .composer-button.cancel {
            border: 1px solid #e2e8f0;

            background: #ffffff;
            color: #475569;
        }

        .composer-button.send {
            border: 0;

            background: #2563eb;
            color: #ffffff;
        }

        /* ============================================================
           SELECTION TOOLBAR
        ============================================================ */

        .selection-toolbar {
            position: fixed;

            display: none;

            z-index: 999999;

            align-items: center;
            gap: 4px;

            padding: 5px;

            border: 1px solid #cbd5e1;
            border-radius: 9px;

            background: #0f172a;

            box-shadow:
                0 8px 30px rgba(15, 23, 42, .25);
        }

        .selection-toolbar.show {
            display: flex;
        }

        .selection-tool-button {
            height: 31px;

            padding: 0 9px;

            display: inline-flex;
            align-items: center;
            gap: 5px;

            border: 0;
            border-radius: 6px;

            background: transparent;
            color: #ffffff;

            font-size: 10px;
            font-weight: 700;
        }

        .selection-tool-button:hover {
            background: rgba(255, 255, 255, .12);
        }

        .selection-tool-button.revision {
            color: #fde68a;
        }

        /* ============================================================
           READONLY
        ============================================================ */

        .readonly-banner {
            display: none;

            align-items: center;
            gap: 7px;

            padding: 7px 14px;

            background: #fffbeb;

            border-bottom: 1px solid #fde68a;

            color: #92400e;

            font-size: 10px;
            font-weight: 600;
        }

        .readonly-mode .readonly-banner {
            display: flex;
        }

        /* ============================================================
           LOADING
        ============================================================ */

        .loading-overlay {
            position: fixed;
            inset: 0;

            z-index: 9999999;

            display: none;
            align-items: center;
            justify-content: center;

            background: rgba(15, 23, 42, .35);

            backdrop-filter: blur(3px);
        }

        .loading-overlay.show {
            display: flex;
        }

        .loading-card {
            width: min(350px, calc(100% - 40px));

            padding: 25px;

            border-radius: 14px;

            background: #ffffff;

            text-align: center;

            box-shadow:
                0 20px 50px rgba(15, 23, 42, .2);
        }

        .loading-spinner {
            width: 34px;
            height: 34px;

            margin: 0 auto 12px;

            border: 3px solid #dbeafe;
            border-top-color: #2563eb;

            border-radius: 50%;

            animation: rppm-spin .7s linear infinite;
        }

        @keyframes rppm-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-title {
            font-size: 13px;
            font-weight: 800;
        }

        .loading-message {
            margin-top: 5px;

            font-size: 10px;
            color: #64748b;
        }

        /* ============================================================
           TOAST
        ============================================================ */

        .toast {
            position: fixed;

            right: 20px;
            bottom: 20px;

            z-index: 9999998;

            width: min(360px, calc(100% - 40px));

            padding: 13px 15px;

            border-radius: 10px;

            background: #0f172a;
            color: #ffffff;

            box-shadow:
                0 10px 35px rgba(15, 23, 42, .25);

            opacity: 0;
            transform: translateY(15px);

            pointer-events: none;

            transition: .2s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-title {
            font-size: 11px;
            font-weight: 800;
        }

        .toast-message {
            margin-top: 3px;

            font-size: 10px;
            line-height: 1.5;
        }

        /* ============================================================
           KOMENTAR COMPACT / OVERLAY
        ============================================================ */
        .rppm-workspace .comments-panel {
            width: 280px;
            max-width: min(280px, 82vw);
        }

        .rppm-workspace .comments-header {
            height: 46px;
            padding: 0 10px;
        }

        .rppm-workspace .comments-header-title {
            font-size: 11px;
            gap: 5px;
        }

        .rppm-workspace .comments-body {
            padding: 8px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .rppm-workspace .comment-composer {
            padding: 8px;
        }

        .rppm-workspace .comment-textarea {
            min-height: 58px;
            max-height: 90px;
            resize: vertical;
        }

        /* Scroll komentar tidak ikut menggeser halaman dokumen. */
        .rppm-workspace .comments-panel,
        .rppm-workspace .comments-body {
            overscroll-behavior: contain;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */

        @media (max-width: 1250px) {
            .rppm-workspace,
            .rppm-workspace.comments-open {
                grid-template-columns: 135px minmax(0, 1fr);
            }

            .rppm-app {
                --sidebar-width: 260px;
            }
        }

        @media (max-width: 900px) {
            .rppm-app {
                --sidebar-width: 0px;

                margin-left: 0;
                width: 100%;
            }

            .page-dock {
                display: none;
            }

            .rppm-workspace,
            .rppm-workspace.comments-open {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 600px) {
            .rppm-header {
                padding: 0 10px;
            }

            .rppm-subtitle {
                display: none;
            }

            .header-button span {
                display: none;
            }

            .rppm-workspace,
            .rppm-workspace.comments-open {
                grid-template-columns: minmax(0, 1fr);
            }

            .comments-open .comments-panel {
                position: fixed;
                inset: 118px 0 0 0;

                z-index: 2000;

                width: 100%;
            }

            .docx-stage {
                padding: 15px 8px 50px;
            }

            .file-name {
                max-width: 180px;
            }
        }

        /* ============================================================
           PRINT
        ============================================================ */

        @media print {
            body {
                background: #ffffff !important;
            }

            .rppm-app {
                margin: 0 !important;
                width: 100% !important;
            }

            .rppm-header,
            .editor-toolbar,
            .file-info-bar,
            .page-dock,
            .comments-panel,
            .selection-toolbar,
            .readonly-banner,
            .toast,
            .loading-overlay {
                display: none !important;
            }

            .rppm-workspace,
            .rppm-workspace.comments-open {
                display: block !important;
            }

            .document-workspace {
                overflow: visible !important;
            }

            .docx-stage {
                display: block !important;
                padding: 0 !important;
            }

            #docx-preview {
                width: auto !important;
            }

            #docx-preview .docx {
                margin: 0 auto !important;

                box-shadow: none !important;

                page-break-after: always;
            }

            .revision-mark {
                box-shadow:
                    inset 0 -2px 0 #f59e0b !important;
            }
        }
    </style>
</head>

<body class="{{ $isGuru ? 'guru-mode' : 'readonly-mode' }}">

    {{-- ================================================================
         SIDEBAR
    ================================================================= --}}

    @include('components.sidebar-beranda', [
        'linkBackButton' => url()->previous(),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
        'headerSideNav' => 'RPPM',
        'schoolName' => $schoolName,
        'schoolId' => $schoolId,
    ])

    <main class="rppm-app">

        {{-- ============================================================
             HEADER
        ============================================================= --}}

        <header class="rppm-header">

            <div class="rppm-header-left">

                <div class="rppm-title-wrap">

                    <h1 class="rppm-title">
                        RPPM
                    </h1>

                    <div class="rppm-subtitle">
                        Rencana Pelaksanaan Pembelajaran Mingguan
                    </div>

                </div>

            </div>


            <div class="rppm-header-actions">

                @if ($documentId && $downloadRoute)

                    <a
                        href="{{ $downloadRoute }}"
                        class="header-button"
                    >
                        <i class="fa-solid fa-download"></i>
                        <span>Download</span>
                    </a>

                @endif

                @if ($isGuru)
                    <button
                        type="button"
                        class="header-button"
                        id="uploadButton"
                    >
                        <i class="fa-solid fa-upload"></i>
                        <span>Upload RPPM</span>
                    </button>

                    @if ($documentId)
                        <button
                            type="button"
                            class="header-button"
                            id="saveArchiveButton"
                        >
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Simpan ke Drive</span>
                        </button>
                    @endif
                @endif

                <select
                    id="teacherDocumentsDropdown"
                    class="header-button academic-document-select"
                    aria-label="Dokumen Guru"
                >
                    <option value="">Dokumen Guru</option>
                </select>

                <select
                    id="archiveDocumentsDropdown"
                    class="header-button academic-document-select"
                    aria-label="Arsip Tersimpan"
                >
                    <option value="">Arsip Tersimpan</option>
                </select>


                @if ($documentId)

                    <button
                        type="button"
                        class="header-button"
                        id="toggleCommentsButton"
                    >
                        <i class="fa-regular fa-comments"></i>
                        <span>Komentar</span>
                    </button>

                @endif


                <button
                    type="button"
                    class="header-button"
                    id="fullscreenButton"
                >
                    <i class="fa-solid fa-expand"></i>
                    <span>Fullscreen</span>
                </button>


                <button
                    type="button"
                    class="header-button"
                    id="printButton"
                >
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </button>


                @if (
                    $documentId
                    && $isGuru
                    && $deleteRoute
                )

                    <button
                        type="button"
                        class="header-button danger"
                        id="clearButton"
                    >
                        <i class="fa-regular fa-trash-can"></i>
                        <span>Hapus</span>
                    </button>

                @endif

            </div>

        </header>


        {{-- ============================================================
             EDITOR TOOLBAR
        ============================================================= --}}

        <div class="editor-toolbar">

            <div class="toolbar-group">

                @if ($isGuru)

                    <button
                        type="button"
                        class="command-button guru-only"
                        data-command="undo"
                        title="Undo"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>

                    <button
                        type="button"
                        class="command-button guru-only"
                        data-command="redo"
                        title="Redo"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>

                @endif

            </div>


            @if ($isGuru)

                <div class="toolbar-group guru-only">

                    <select
                        id="fontName"
                        class="toolbar-select"
                    >
                        <option value="Arial">
                            Arial
                        </option>

                        <option value="Calibri">
                            Calibri
                        </option>

                        <option value="Times New Roman">
                            Times New Roman
                        </option>

                        <option value="Verdana">
                            Verdana
                        </option>

                        <option value="Georgia">
                            Georgia
                        </option>
                    </select>


                    <select
                        id="fontSize"
                        class="toolbar-select"
                    >
                        <option value="1">8</option>
                        <option value="2">10</option>
                        <option value="3" selected>12</option>
                        <option value="4">14</option>
                        <option value="5">18</option>
                        <option value="6">24</option>
                        <option value="7">32</option>
                    </select>

                </div>

            @endif


            <div class="toolbar-group">

                <button
                    type="button"
                    class="command-button"
                    data-command="bold"
                    title="Bold"
                >
                    <i class="fa-solid fa-bold"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="italic"
                    title="Italic"
                >
                    <i class="fa-solid fa-italic"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="underline"
                    title="Underline"
                >
                    <i class="fa-solid fa-underline"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="strikeThrough"
                    title="Coret"
                >
                    <i class="fa-solid fa-strikethrough"></i>
                </button>

            </div>


            <div class="toolbar-group">

                <button
                    type="button"
                    class="command-button"
                    data-command="justifyLeft"
                    title="Rata kiri"
                >
                    <i class="fa-solid fa-align-left"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="justifyCenter"
                    title="Rata tengah"
                >
                    <i class="fa-solid fa-align-center"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="justifyRight"
                    title="Rata kanan"
                >
                    <i class="fa-solid fa-align-right"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="justifyFull"
                    title="Rata penuh"
                >
                    <i class="fa-solid fa-align-justify"></i>
                </button>

            </div>


            <div class="toolbar-group">

                <button
                    type="button"
                    class="command-button"
                    data-command="insertUnorderedList"
                    title="Bullet"
                >
                    <i class="fa-solid fa-list-ul"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="insertOrderedList"
                    title="Numbering"
                >
                    <i class="fa-solid fa-list-ol"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="outdent"
                    title="Kurangi indent"
                >
                    <i class="fa-solid fa-outdent"></i>
                </button>

                <button
                    type="button"
                    class="command-button"
                    data-command="indent"
                    title="Tambah indent"
                >
                    <i class="fa-solid fa-indent"></i>
                </button>

            </div>


            <div class="toolbar-group">

                <button
                    type="button"
                    class="command-button"
                    id="zoomOut"
                    title="Zoom out"
                >
                    <i class="fa-solid fa-minus"></i>
                </button>

                <span
                    id="zoomValue"
                    style="
                        min-width:42px;
                        text-align:center;
                        font-size:10px;
                        font-weight:700;
                    "
                >
                    100%
                </span>

                <button
                    type="button"
                    class="command-button"
                    id="zoomIn"
                    title="Zoom in"
                >
                    <i class="fa-solid fa-plus"></i>
                </button>

            </div>

        </div>


        {{-- ============================================================
             READONLY BANNER
        ============================================================= --}}

        @if (!$isGuru && $documentId)

            <div class="readonly-banner">

                <i class="fa-solid fa-eye"></i>

                <span>
                    Mode tampilan. Anda dapat memberikan komentar,
                    membalas komentar, menyelesaikan komentar,
                    dan menandai bagian RPPM untuk revisi.
                </span>

            </div>

        @endif


        {{-- ============================================================
             FILE INFO
        ============================================================= --}}

        <div
            class="file-info-bar"
            id="fileInfoBar"
        >

            <div class="file-info-left">

                <div class="file-icon">
                    <i class="fa-solid fa-file-word"></i>
                </div>

                <div class="file-meta">

                    <div
                        class="file-name"
                        id="fileName"
                    >
                        {{ $existingFileName }}
                    </div>

                    <div
                        class="file-status"
                        id="fileStatus"
                    >
                        Dokumen RPPM
                    </div>

                </div>

            </div>


            <div
                class="save-status"
                id="saveStatus"
            >
                Tersimpan
            </div>

        </div>


        {{-- ============================================================
             WORKSPACE
        ============================================================= --}}

        <section
            class="rppm-workspace"
            id="workspace"
        >

            {{-- ========================================================
                 PAGE DOCK
            ========================================================= --}}

            <aside class="page-dock">

                <div class="page-dock-header">
                    Halaman
                    <span id="pageCount">0</span>
                </div>

                <div id="pageList"></div>

            </aside>


            {{-- ========================================================
                 DOCUMENT AREA
            ========================================================= --}}

            <section class="document-workspace">

                <div
                    class="empty-state"
                    id="emptyState"
                >

                    <div class="empty-card">

                        <div class="empty-icon">
                            <i class="fa-solid fa-file-word"></i>
                        </div>

                        @if ($isGuru)

                            <h2>
                                Belum ada dokumen RPPM
                            </h2>

                            <p>
                                Upload dokumen RPPM dalam format
                                DOCX untuk mulai mengedit,
                                menyimpan, dan mengelola dokumen.
                            </p>

                            <div
                                class="upload-dropzone"
                                id="dropZone"
                            >

                                <i class="fa-solid fa-cloud-arrow-up"></i>

                                <strong>
                                    Tarik file DOCX ke sini
                                </strong>

                                <span>
                                    atau pilih file dari komputer.
                                    Maksimal 20 MB.
                                </span>

                                <button
                                    type="button"
                                    class="upload-main-button"
                                    id="uploadMainButton"
                                >
                                    <i class="fa-solid fa-upload"></i>
                                    Upload RPPM
                                </button>

                            </div>

                        @else

                            <h2>
                                Belum ada dokumen RPPM
                            </h2>

                            <p>
                                Belum tersedia dokumen RPPM
                                untuk ditampilkan.
                            </p>

                        @endif

                    </div>

                </div>


                <div
                    class="docx-stage"
                    id="docxStage"
                >

                    <div
                        id="docx-preview"
                        contenteditable="{{ $isGuru ? 'true' : 'false' }}"
                        spellcheck="false"
                    >{!! $existingContent !!}</div>

                </div>

            </section>


            {{-- ========================================================
                 COMMENTS PANEL
            ========================================================= --}}

            <aside
                class="comments-panel"
                id="commentsPanel"
            >

                <div class="comments-header">

                    <div class="comments-header-title">

                        <i class="fa-regular fa-comments"></i>

                        <span>
                            Komentar RPPM
                        </span>

                        <span
                            class="comments-count"
                            id="commentsCount"
                        >
                            0
                        </span>

                    </div>


                    <button
                        type="button"
                        class="close-comments-button"
                        id="closeCommentsButton"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                </div>


                <div
                    class="comments-body"
                    id="commentsBody"
                >
                    Memuat komentar...
                </div>


                {{-- Composer sengaja permanen --}}
                <div
                    class="comment-composer"
                    id="commentComposer"
                >

                    <div
                        class="selection-preview"
                        id="selectionPreview"
                    ></div>


                    <textarea
                        class="comment-textarea"
                        id="commentTextarea"
                        placeholder="Tulis komentar pada RPPM..."
                    ></textarea>


                    <div class="composer-actions">

                        <button
                            type="button"
                            class="composer-button cancel"
                            id="cancelCommentButton"
                        >
                            Bersihkan
                        </button>

                        <button
                            type="button"
                            class="composer-button send"
                            id="submitCommentButton"
                        >
                            <i class="fa-solid fa-paper-plane"></i>
                            Kirim
                        </button>

                    </div>

                </div>

            </aside>

        </section>

    </main>


    {{-- ================================================================
         SELECTION TOOLBAR
    ================================================================= --}}

    <div
        class="selection-toolbar"
        id="selectionToolbar"
    >

        <button
            type="button"
            class="selection-tool-button"
            id="selectionCommentButton"
        >
            <i class="fa-regular fa-comment"></i>
            Komentar
        </button>


        @if (!$isGuru)

            <button
                type="button"
                class="selection-tool-button revision"
                id="selectionRevisionButton"
            >
                <i class="fa-solid fa-rotate"></i>
                Tandai Revisi
            </button>

        @endif

    </div>


    {{-- ================================================================
         HIDDEN UPLOAD
    ================================================================= --}}

    @if ($isGuru)

        <input
            type="file"
            id="wordFile"
            accept=".docx"
            hidden
        >

        <button
            type="button"
            id="uploadButton"
            hidden
        >
            Upload
        </button>

    @endif


    {{-- ================================================================
         LOADING
    ================================================================= --}}

    <div
        class="loading-overlay"
        id="loadingOverlay"
    >

        <div class="loading-card">

            <div class="loading-spinner"></div>

            <div
                class="loading-title"
                id="loadingTitle"
            >
                Memproses RPPM...
            </div>

            <div
                class="loading-message"
                id="loadingMessage"
            >
                Mohon tunggu.
            </div>

        </div>

    </div>


    {{-- ================================================================
         TOAST
    ================================================================= --}}

    <div
        class="toast"
        id="toast"
    >

        <div
            class="toast-title"
            id="toastTitle"
        >
            Informasi
        </div>

        <div
            class="toast-message"
            id="toastMessage"
        ></div>

    </div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    'use strict';


    /* ================================================================
       DATA
    ================================================================ */

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content')
        || '';


    const uploadRoute =
        @json($uploadRoute);


    const editRouteTemplate =
        @json($editRouteTemplate);


    const saveRoute =
        @json($saveRoute);


    const downloadRoute =
        @json($downloadRoute);


    const deleteRoute =
        @json($deleteRoute);


    const commentStoreRoute =
        @json($commentStoreRoute);


    const commentReplyRouteTemplate =
        @json($commentReplyRouteTemplate);


    const commentResolveRouteTemplate =
        @json($commentResolveRouteTemplate);


    const commentDeleteRouteTemplate =
        @json($commentDeleteRouteTemplate);


    const existingFileUrl =
        @json($fileRoute);


    const existingDocumentId =
        @json($documentId);


    const existingContent =
        @json($existingContent);


    const existingFileName =
        @json($existingFileName);


    const academicBrowseRoute =
        @json($academicBrowseRoute);


    const academicSaveArchiveRoute =
        @json($academicSaveArchiveRoute);


    const isGuru =
        @json($isGuru);


    let comments =
        Array.isArray(window.__rppmComments)
            ? window.__rppmComments
            : @json($rppmCommentsPayload);


    /* ================================================================
       DOM
    ================================================================ */

    const workspace =
        document.getElementById('workspace');


    const documentWorkspace =
        document.querySelector('.document-workspace');


    const docxStage =
        document.getElementById('docxStage');


    const docxPreview =
        document.getElementById('docx-preview');


    const emptyState =
        document.getElementById('emptyState');


    const pageDock =
        document.querySelector('.page-dock');


    const pageList =
        document.getElementById('pageList');


    const pageCount =
        document.getElementById('pageCount');


    const commentsPanel =
        document.getElementById('commentsPanel');


    const commentsBody =
        document.getElementById('commentsBody');


    const commentsCount =
        document.getElementById('commentsCount');


    const toggleCommentsButton =
        document.getElementById('toggleCommentsButton');


    const closeCommentsButton =
        document.getElementById('closeCommentsButton');


    const selectionToolbar =
        document.getElementById('selectionToolbar');


    const selectionCommentButton =
        document.getElementById('selectionCommentButton');


    const selectionRevisionButton =
        document.getElementById('selectionRevisionButton');


    const commentComposer =
        document.getElementById('commentComposer');


    const selectionPreview =
        document.getElementById('selectionPreview');


    const commentTextarea =
        document.getElementById('commentTextarea');


    const cancelCommentButton =
        document.getElementById('cancelCommentButton');


    const submitCommentButton =
        document.getElementById('submitCommentButton');


    const uploadButton =
        document.getElementById('uploadButton');


    const uploadMainButton =
        document.getElementById('uploadMainButton');


    const dropZone =
        document.getElementById('dropZone');


    const wordFile =
        document.getElementById('wordFile');


    const fileInfoBar =
        document.getElementById('fileInfoBar');


    const fileName =
        document.getElementById('fileName');


    const fileStatus =
        document.getElementById('fileStatus');


    const teacherDocumentsDropdown =
        document.getElementById('teacherDocumentsDropdown');


    const archiveDocumentsDropdown =
        document.getElementById('archiveDocumentsDropdown');


    const saveArchiveButton =
        document.getElementById('saveArchiveButton');


    const saveStatus =
        document.getElementById('saveStatus');


    const loadingOverlay =
        document.getElementById('loadingOverlay');


    const loadingTitle =
        document.getElementById('loadingTitle');


    const loadingMessage =
        document.getElementById('loadingMessage');


    const toast =
        document.getElementById('toast');


    const toastTitle =
        document.getElementById('toastTitle');


    const toastMessage =
        document.getElementById('toastMessage');


    const fullscreenButton =
        document.getElementById('fullscreenButton');


    const printButton =
        document.getElementById('printButton');


    const clearButton =
        document.getElementById('clearButton');


    const zoomOut =
        document.getElementById('zoomOut');


    const zoomIn =
        document.getElementById('zoomIn');


    const zoomValue =
        document.getElementById('zoomValue');


    const fontName =
        document.getElementById('fontName');


    const fontSize =
        document.getElementById('fontSize');


    /* ================================================================
       STATE
    ================================================================ */

    let selectedRange = null;

    let selectedText = '';

    let selectedPageNumber = null;

    let zoom = 1;

    let autoSaveTimer = null;

    let toastTimer = null;

    let isSaving = false;


    /* ================================================================
       HELPERS
    ================================================================ */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    function showToast(
        title,
        message,
        type = 'normal'
    ) {

        if (!toast) {
            return;
        }

        clearTimeout(toastTimer);

        toastTitle.textContent =
            title || 'Informasi';

        toastMessage.textContent =
            message || '';

        toast.classList.add('show');

        if (type === 'error') {
            toast.style.background = '#b91c1c';
        } else if (type === 'success') {
            toast.style.background = '#166534';
        } else {
            toast.style.background = '#0f172a';
        }

        toastTimer =
            setTimeout(function () {
                toast.classList.remove('show');
            }, 3500);
    }


    function showLoading(
        title = 'Memproses RPPM...',
        message = 'Mohon tunggu.'
    ) {

        if (!loadingOverlay) {
            return;
        }

        loadingTitle.textContent =
            title;

        loadingMessage.textContent =
            message;

        loadingOverlay.classList.add('show');
    }


    function hideLoading() {

        loadingOverlay?.classList.remove('show');
    }


    function showSaveStatus(
        text = 'Tersimpan',
        type = 'success'
    ) {

        if (!saveStatus) {
            return;
        }

        saveStatus.textContent =
            text;

        saveStatus.className =
            'save-status show ' + type;

        setTimeout(function () {
            saveStatus.classList.remove('show');
        }, 1800);
    }


    function roleLabel(role) {

        const value =
            String(role || '')
                .toLowerCase()
                .trim();

        if (value === 'guru') {
            return 'Guru';
        }

        if (value === 'admin sekolah') {
            return 'Admin Sekolah';
        }

        if (value === 'kepala sekolah') {
            return 'Kepala Sekolah';
        }

        if (value === 'wakil kepala sekolah') {
            return 'Wakil Kepala Sekolah';
        }

        return role || 'Pengguna';
    }


    function commentUserName(comment) {

        return (
            comment?.user_name
            || comment?.user?.name
            || comment?.user?.username
            || 'Pengguna'
        );
    }


    function commentRole(comment) {

        return roleLabel(
            comment?.role
            || comment?.user?.role
            || ''
        );
    }


    function commentText(comment) {

        return (
            comment?.comment_text
            || comment?.comment
            || comment?.content
            || comment?.body
            || ''
        );
    }


    function isRevisionComment(comment) {

        return String(
            comment?.anchor_key || ''
        ).startsWith('revision:');
    }


    function isResolvedComment(comment) {

        return Boolean(
            comment?.resolved
            || comment?.resolved_at
        );
    }


    function routeForTemplate(
        template,
        id
    ) {

        if (!template) {
            return null;
        }

        return template.replace(
            '__COMMENT__',
            encodeURIComponent(String(id))
        );
    }


    function makeRevisionKey(
        text,
        pageNumber
    ) {

        let hash = 0;

        const value =
            `${pageNumber || 1}:${text || ''}`;

        for (
            let i = 0;
            i < value.length;
            i++
        ) {

            hash =
                ((hash << 5) - hash)
                + value.charCodeAt(i);

            hash |= 0;
        }

        return (
            'revision:'
            + Math.abs(hash)
        );
    }


    function getDocxPages() {

        if (!docxPreview) {
            return [];
        }

        /*
         * Hanya ambil halaman DOCX yang menjadi child langsung
         * dari preview. Jangan menghitung .docx nested sebagai halaman baru.
         */
        /*
         * docx-preview biasanya membuat struktur:
         * #docx-preview > .docx-wrapper > .docx (halaman).
         * Jadi jangan hanya membaca children langsung dari preview,
         * karena itu akan menghasilkan 0 halaman.
         */
        const wrapper =
            docxPreview.querySelector('.docx-wrapper');

        if (wrapper) {
            const pages = Array.from(
                wrapper.children
            ).filter(function (element) {
                return element.classList.contains('docx');
            });

            if (pages.length) {
                return pages;
            }
        }

        /* Fallback untuk HTML hasil render yang tidak memakai wrapper. */
        return Array.from(
            docxPreview.querySelectorAll('.docx')
        ).filter(function (element) {
            return !element.parentElement?.closest('.docx');
        });
    }


    function getSelectionPageNumber() {

        if (!selectedRange) {
            return null;
        }

        let node =
            selectedRange.commonAncestorContainer;

        if (
            node.nodeType ===
            Node.TEXT_NODE
        ) {
            node = node.parentElement;
        }

        const page =
            node?.closest?.('.docx');

        if (!page) {
            return null;
        }

        const pages =
            getDocxPages();

        const index =
            pages.indexOf(page);

        return index >= 0
            ? index + 1
            : null;
    }


    /* ================================================================
       COMMENT COMPOSER LAYOUT
    ================================================================ */

    function setupCommentComposerLayout() {

        if (
            !commentsPanel
            || !commentsBody
            || !commentComposer
        ) {
            return;
        }

        if (
            commentComposer.parentElement
            !== commentsPanel
        ) {

            commentsPanel.appendChild(
                commentComposer
            );
        }

        commentsPanel.style.display =
            'flex';

        commentsPanel.style.flexDirection =
            'column';

        commentsPanel.style.minHeight =
            '0';

        commentsPanel.style.height =
            '100%';

        commentsBody.style.flex =
            '1 1 auto';

        commentsBody.style.minHeight =
            '0';

        commentsBody.style.overflowY =
            'auto';

        commentsBody.style.overflowX =
            'hidden';

        commentsBody.style.overscrollBehavior =
            'contain';

        commentComposer.classList.add(
            'show'
        );

        commentComposer.style.display =
            'block';

        commentComposer.style.flex =
            '0 0 auto';

        commentComposer.style.position =
            'sticky';

        commentComposer.style.bottom =
            '0';

        commentComposer.style.zIndex =
            '30';

        commentComposer.style.background =
            '#fff';

        commentComposer.style.borderTop =
            '1px solid #e5e7eb';
    }


    /* ================================================================
       COMMENTS
    ================================================================ */

    function updateCommentsCount() {

        if (commentsCount) {
            commentsCount.textContent =
                comments.length;
        }
    }


    function buildCommentCard(comment) {

        const revision =
            isRevisionComment(comment);

        const resolved =
            isResolvedComment(comment);

        const userName =
            commentUserName(comment);

        const initial =
            String(userName)
                .trim()
                .charAt(0)
                .toUpperCase()
            || 'P';

        const replies =
            Array.isArray(comment.replies)
                ? comment.replies
                : [];

        const selected =
            comment.selected_text
            || '';

        let repliesHtml = '';

        if (replies.length) {

            repliesHtml =
                '<div class="comment-replies">';

            replies.forEach(function (reply) {

                repliesHtml += `
                    <div class="comment-reply">

                        <div>
                            <span class="reply-user">
                                ${escapeHtml(
                                    reply.user_name
                                    || 'Pengguna'
                                )}
                            </span>

                            <span class="reply-role">
                                ${escapeHtml(
                                    roleLabel(
                                        reply.role
                                    )
                                )}
                            </span>
                        </div>

                        <div class="reply-text">
                            ${escapeHtml(
                                reply.comment_text
                                || ''
                            )}
                        </div>

                    </div>
                `;
            });

            repliesHtml +=
                '</div>';
        }


        const actionButtons = `
            <div class="comment-actions-row">

                <button
                    type="button"
                    class="comment-mini-button"
                    data-action="reply"
                    data-id="${comment.id}"
                >
                    <i class="fa-solid fa-reply"></i>
                    Balas
                </button>

                <button
                    type="button"
                    class="comment-mini-button"
                    data-action="resolve"
                    data-id="${comment.id}"
                >
                    <i class="fa-solid fa-check"></i>
                    ${resolved ? 'Buka' : 'Selesai'}
                </button>

                <button
                    type="button"
                    class="comment-mini-button danger"
                    data-action="delete"
                    data-id="${comment.id}"
                >
                    <i class="fa-regular fa-trash-can"></i>
                </button>

            </div>
        `;


        return `
            <div
                class="comment-card
                    ${revision ? 'is-revision' : ''}
                    ${resolved ? 'resolved' : ''}"
                data-comment-card="${comment.id}"
            >

                ${
                    revision
                        ? `
                            <div class="comment-revision-label">
                                <i class="fa-solid fa-rotate"></i>
                                REVISI RPPM
                            </div>
                        `
                        : ''
                }


                <div class="comment-user">

                    <div class="comment-avatar">
                        ${escapeHtml(initial)}
                    </div>

                    <div class="comment-user-info">

                        <strong>
                            ${escapeHtml(userName)}
                        </strong>

                        <span>
                            ${escapeHtml(
                                commentRole(comment)
                            )}

                            ${
                                comment.created_at
                                    ? ' · ' +
                                      escapeHtml(
                                          comment.created_at
                                      )
                                    : ''
                            }
                        </span>

                    </div>

                </div>


                ${
                    selected
                        ? `
                            <div
                                class="comment-selected-text"
                            >
                                ${escapeHtml(selected)}
                            </div>
                        `
                        : ''
                }


                <div class="comment-content">
                    ${escapeHtml(
                        commentText(comment)
                    )}
                </div>


                ${repliesHtml}


                <div
                    class="reply-box"
                    data-reply-box="${comment.id}"
                >

                    <textarea
                        class="reply-input"
                        data-reply-input="${comment.id}"
                        placeholder="Tulis balasan..."
                    ></textarea>

                    <div
                        style="
                            display:flex;
                            justify-content:flex-end;
                            gap:5px;
                            margin-top:5px;
                        "
                    >

                        <button
                            type="button"
                            class="comment-mini-button"
                            data-action="cancel-reply"
                            data-id="${comment.id}"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            class="comment-mini-button"
                            data-action="submit-reply"
                            data-id="${comment.id}"
                        >
                            Kirim
                        </button>

                    </div>

                </div>


                ${actionButtons}

            </div>
        `;
    }


    function rerenderComments() {

        if (!commentsBody) {
            return;
        }

        updateCommentsCount();

        if (!comments.length) {

            commentsBody.innerHTML = `
                <div class="comment-empty">

                    <i class="fa-regular fa-comment-dots"></i>

                    <strong>
                        Belum ada komentar
                    </strong>

                    <span>
                        Blok teks pada RPPM untuk membuat
                        komentar atau menandai bagian
                        yang perlu direvisi.
                    </span>

                </div>
            `;

            return;
        }

        commentsBody.innerHTML =
            comments
                .map(buildCommentCard)
                .join('');
    }


    function openCommentsPanel() {

        workspace?.classList.add(
            'comments-open'
        );

        setupCommentComposerLayout();
    }


    function closeCommentsPanel() {

        workspace?.classList.remove(
            'comments-open'
        );

        clearSelectionToolbar();
    }


    /* ================================================================
       SELECTION
    ================================================================ */

    function clearSelectionToolbar() {

        selectionToolbar?.classList.remove(
            'show'
        );
    }


    function showCommentComposer(
        focus = true
    ) {

        openCommentsPanel();

        if (selectionPreview) {

            selectionPreview.textContent =
                selectedText
                    || 'Komentar umum';

            selectionPreview.style.display =
                '';
        }

        if (commentComposer) {

            commentComposer.classList.add(
                'show'
            );

            commentComposer.style.display =
                'block';
        }

        if (focus) {

            setTimeout(function () {
                commentTextarea?.focus();
            }, 50);
        }
    }


    function resetCommentComposer() {

        if (selectionPreview) {

            selectionPreview.textContent =
                '';

            selectionPreview.style.display =
                '';
        }

        if (commentTextarea) {
            commentTextarea.value = '';
        }

        if (commentComposer) {

            commentComposer.classList.add(
                'show'
            );

            commentComposer.style.display =
                'block';
        }
    }


    function hideCommentComposer() {

        resetCommentComposer();
    }


    /*
     * ================================================================
     * CAPTURE SELECTION
     *
     * PERBAIKAN PENTING:
     * - Selection harus berasal dari DOCX.
     * - Selection tetap disimpan sebelum toolbar diklik.
     * - Toolbar menggunakan fixed position.
     * - Revisi tidak bergantung pada focus textarea.
     * ================================================================
     */

    function captureSelection() {

        const selection =
            window.getSelection();

        if (
            !selection
            || selection.rangeCount === 0
        ) {
            return;
        }

        const text =
            selection.toString().trim();

        if (!text) {
            return;
        }

        const range =
            selection.getRangeAt(0);

        let container =
            range.commonAncestorContainer;

        if (
            container.nodeType ===
            Node.TEXT_NODE
        ) {
            container =
                container.parentElement;
        }

        if (
            !container
            || !docxPreview
            || !docxPreview.contains(container)
        ) {
            return;
        }

        selectedRange =
            range.cloneRange();

        selectedText =
            text;

        selectedPageNumber =
            getSelectionPageNumber();

        if (
            commentComposer
            && commentComposer.classList.contains('show')
            && selectionPreview
        ) {

            selectionPreview.textContent =
                selectedText;
        }

        const rect =
            range.getBoundingClientRect();

        if (
            !rect
            || (
                rect.width === 0
                && rect.height === 0
            )
        ) {
            return;
        }

        if (!selectionToolbar) {
            return;
        }

        /*
         * Pastikan toolbar sudah display sebelum
         * membaca offsetWidth / offsetHeight.
         */

        selectionToolbar.classList.add(
            'show'
        );

        const toolbarWidth =
            selectionToolbar.offsetWidth;

        const toolbarHeight =
            selectionToolbar.offsetHeight;

        let left =
            rect.left
            + (
                rect.width / 2
            )
            - (
                toolbarWidth / 2
            );

        let top =
            rect.top
            - toolbarHeight
            - 10;

        if (top < 8) {
            top =
                rect.bottom + 10;
        }

        left =
            Math.max(
                8,
                Math.min(
                    window.innerWidth
                        - toolbarWidth
                        - 8,
                    left
                )
            );

        top =
            Math.max(
                8,
                Math.min(
                    window.innerHeight
                        - toolbarHeight
                        - 8,
                    top
                )
            );

        selectionToolbar.style.left =
            left + 'px';

        selectionToolbar.style.top =
            top + 'px';
    }


    document.addEventListener(
        'selectionchange',
        function () {

            clearTimeout(
                window.__rppmSelectionTimer
            );

            window.__rppmSelectionTimer =
                setTimeout(
                    captureSelection,
                    80
                );
        }
    );


    selectionToolbar?.addEventListener(
        'mousedown',
        function (event) {

            /*
             * Jangan biarkan klik toolbar menghapus
             * selection browser.
             */
            event.preventDefault();
        }
    );


    selectionCommentButton?.addEventListener(
        'click',
        function () {

            if (!selectedText) {

                showToast(
                    'Pilih teks',
                    'Blok bagian RPPM yang ingin diberi komentar.',
                    'error'
                );

                return;
            }

            showCommentComposer(true);
        }
    );


    selectionRevisionButton?.addEventListener(
        'click',
        async function () {

            if (isGuru) {
                return;
            }

            if (!selectedText) {

                showToast(
                    'Pilih teks',
                    'Blok bagian RPPM yang ingin ditandai revisi.',
                    'error'
                );

                return;
            }

            /*
             * Simpan selection sebelum toolbar dihapus.
             */

            const text =
                selectedText;

            const pageNumber =
                selectedPageNumber
                || getSelectionPageNumber()
                || 1;

            const revisionKey =
                makeRevisionKey(
                    text,
                    pageNumber
                );

            clearSelectionToolbar();

            await postComment({
                revision: true,
                commentText:
                    'Revisi diperlukan pada bagian RPPM yang ditandai.',
                selectedText:
                    text,
                pageNumber:
                    pageNumber,
                anchorKey:
                    revisionKey,
            });
        }
    );


    /* ================================================================
       COMMENT API
    ================================================================ */

    async function postComment(
        options = {}
    ) {

        if (!commentStoreRoute) {

            showToast(
                'Komentar tidak tersedia',
                'Route komentar RPPM belum tersedia.',
                'error'
            );

            return false;
        }

        const commentValue =
            options.commentText
            ?? commentTextarea?.value
            ?? '';

        if (!String(commentValue).trim()) {

            showToast(
                'Komentar kosong',
                'Silakan tulis komentar terlebih dahulu.',
                'error'
            );

            return false;
        }

        const payload = {

            page_number:
                options.pageNumber
                ?? selectedPageNumber
                ?? 1,

            selected_text:
                options.selectedText
                ?? selectedText
                ?? '',

            comment_text:
                commentValue,

            anchor_key:
                options.anchorKey
                ?? null,
        };


        try {

            if (submitCommentButton) {
                submitCommentButton.disabled = true;
            }

            const response =
                await fetch(
                    commentStoreRoute,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        body:
                            JSON.stringify(payload),
                    }
                );


            const data =
                await response
                    .json()
                    .catch(() => ({}));


            if (!response.ok) {

                throw new Error(
                    data.message
                    || 'Komentar RPPM gagal disimpan.'
                );
            }


            const created =
                data.comment
                || data.data
                || data;


            if (
                created
                && created.id
            ) {

                comments.unshift(
                    created
                );
            }


            rerenderComments();

            openCommentsPanel();

            resetCommentComposer();

            clearSelectionToolbar();


            setTimeout(function () {

                commentTextarea?.focus();

            }, 50);


            showToast(
                options.revision
                    ? 'Revisi RPPM ditandai'
                    : 'Komentar terkirim',
                options.revision
                    ? 'Bagian RPPM yang dipilih telah ditandai sebagai revisi.'
                    : 'Komentar RPPM berhasil ditambahkan.',
                'success'
            );


            setTimeout(
                applyAllRevisionHighlights,
                150
            );


            return true;

        } catch (error) {

            showToast(
                'Gagal',
                error.message
                    || 'Komentar RPPM gagal disimpan.',
                'error'
            );

            return false;

        } finally {

            if (submitCommentButton) {
                submitCommentButton.disabled = false;
            }
        }
    }


    submitCommentButton?.addEventListener(
        'click',
        function () {

            postComment({

                pageNumber:
                    selectedPageNumber,

                selectedText:
                    selectedText,

                commentText:
                    commentTextarea?.value
                    || '',
            });
        }
    );


    cancelCommentButton?.addEventListener(
        'click',
        function () {

            resetCommentComposer();

            clearSelectionToolbar();

            setTimeout(function () {

                commentTextarea?.focus();

            }, 50);
        }
    );


    commentTextarea?.addEventListener(
        'keydown',
        function (event) {

            if (
                (event.ctrlKey || event.metaKey)
                && event.key === 'Enter'
            ) {

                event.preventDefault();

                submitCommentButton?.click();
            }
        }
    );


    /* ================================================================
       REPLY
    ================================================================ */

    async function submitReply(
        commentId,
        text
    ) {

        if (!commentReplyRouteTemplate) {

            showToast(
                'Balasan tidak tersedia',
                'Route balasan komentar RPPM belum tersedia.',
                'error'
            );

            return;
        }


        if (!String(text || '').trim()) {

            showToast(
                'Balasan kosong',
                'Tulis balasan terlebih dahulu.',
                'error'
            );

            return;
        }


        const url =
            routeForTemplate(
                commentReplyRouteTemplate,
                commentId
            );


        try {

            const response =
                await fetch(
                    url,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        body:
                            JSON.stringify({
                                comment_text:
                                    text,
                            }),
                    }
                );


            const data =
                await response
                    .json()
                    .catch(() => ({}));


            if (!response.ok) {

                throw new Error(
                    data.message
                    || 'Balasan RPPM gagal disimpan.'
                );
            }


            const parent =
                comments.find(
                    item =>
                        String(item.id)
                        === String(commentId)
                );


            if (parent) {

                if (
                    !Array.isArray(
                        parent.replies
                    )
                ) {
                    parent.replies = [];
                }


                const reply =
                    data.reply
                    || data.data
                    || null;


                if (reply) {

                    parent.replies.push(
                        reply
                    );
                }
            }


            rerenderComments();


            showToast(
                'Balasan terkirim',
                'Balasan komentar RPPM berhasil ditambahkan.',
                'success'
            );

        } catch (error) {

            showToast(
                'Gagal',
                error.message
                    || 'Balasan gagal disimpan.',
                'error'
            );
        }
    }


    /* ================================================================
       RESOLVE
    ================================================================ */

    async function toggleCommentResolved(
        commentId
    ) {

        if (!commentResolveRouteTemplate) {

            showToast(
                'Tidak tersedia',
                'Route penyelesaian komentar RPPM belum tersedia.',
                'error'
            );

            return;
        }


        const comment =
            comments.find(
                item =>
                    String(item.id)
                    === String(commentId)
            );


        if (!comment) {
            return;
        }


        const url =
            routeForTemplate(
                commentResolveRouteTemplate,
                commentId
            );


        try {

            const response =
                await fetch(
                    url,
                    {
                        method: 'POST',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                    }
                );


            const data =
                await response
                    .json()
                    .catch(() => ({}));


            if (!response.ok) {

                throw new Error(
                    data.message
                    || 'Status komentar RPPM gagal diubah.'
                );
            }


            comment.resolved =
                data.resolved
                ?? !isResolvedComment(comment);


            comment.resolved_at =
                data.resolved_at
                ?? (
                    comment.resolved
                        ? new Date().toISOString()
                        : null
                );


            rerenderComments();


            setTimeout(
                applyAllRevisionHighlights,
                100
            );


            showToast(
                comment.resolved
                    ? 'Komentar diselesaikan'
                    : 'Komentar dibuka kembali',
                '',
                'success'
            );

        } catch (error) {

            showToast(
                'Gagal',
                error.message
                    || 'Status komentar gagal diubah.',
                'error'
            );
        }
    }


    /* ================================================================
       DELETE COMMENT
    ================================================================ */

    async function deleteComment(
        commentId
    ) {

        if (!commentDeleteRouteTemplate) {

            showToast(
                'Tidak tersedia',
                'Route hapus komentar RPPM belum tersedia.',
                'error'
            );

            return;
        }


        if (
            !confirm(
                'Hapus komentar RPPM ini?'
            )
        ) {
            return;
        }


        const url =
            routeForTemplate(
                commentDeleteRouteTemplate,
                commentId
            );


        try {

            const response =
                await fetch(
                    url,
                    {
                        method: 'DELETE',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                    }
                );


            const data =
                await response
                    .json()
                    .catch(() => ({}));


            if (!response.ok) {

                throw new Error(
                    data.message
                    || 'Komentar RPPM gagal dihapus.'
                );
            }


            comments =
                comments.filter(
                    item =>
                        String(item.id)
                        !== String(commentId)
                );


            rerenderComments();


            setTimeout(
                applyAllRevisionHighlights,
                100
            );


            showToast(
                'Komentar dihapus',
                'Komentar RPPM berhasil dihapus.',
                'success'
            );

        } catch (error) {

            showToast(
                'Gagal',
                error.message
                    || 'Komentar RPPM gagal dihapus.',
                'error'
            );
        }
    }


    /* ================================================================
       COMMENT EVENT DELEGATION
    ================================================================ */

    commentsBody?.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-action]'
                );


            if (!button) {
                return;
            }


            const action =
                button.dataset.action;


            const id =
                button.dataset.id;


            if (action === 'reply') {

                const box =
                    commentsBody.querySelector(
                        `[data-reply-box="${id}"]`
                    );


                box?.classList.toggle(
                    'show'
                );


                return;
            }


            if (action === 'cancel-reply') {

                const box =
                    commentsBody.querySelector(
                        `[data-reply-box="${id}"]`
                    );


                box?.classList.remove(
                    'show'
                );


                return;
            }


            if (action === 'submit-reply') {

                const input =
                    commentsBody.querySelector(
                        `[data-reply-input="${id}"]`
                    );


                submitReply(
                    id,
                    input?.value || ''
                );


                return;
            }


            if (action === 'resolve') {

                toggleCommentResolved(
                    id
                );


                return;
            }


            if (action === 'delete') {

                deleteComment(
                    id
                );
            }
        }
    );


    /* ================================================================
       REVISION HIGHLIGHT
    ================================================================ */

    function findTextRange(
        root,
        searchText
    ) {

        if (
            !root
            || !searchText
        ) {
            return null;
        }


        const normalizedSearch =
            String(searchText)
                .replace(/\s+/g, ' ')
                .trim();


        if (!normalizedSearch) {
            return null;
        }


        const walker =
            document.createTreeWalker(
                root,
                NodeFilter.SHOW_TEXT
            );


        const nodes = [];

        let node;


        while (
            node =
                walker.nextNode()
        ) {

            if (
                !node.nodeValue
                || !node.nodeValue.trim()
            ) {
                continue;
            }


            if (
                node.parentElement?.closest(
                    '.revision-mark'
                )
            ) {
                continue;
            }


            nodes.push(node);
        }


        /*
         * Buat mapping text node.
         *
         * Spasi dinormalisasi sehingga selection dari
         * Word yang terpecah menjadi beberapa text node
         * tetap dapat ditemukan.
         */

        const nodeData = [];

        let fullText = '';


        nodes.forEach(function (textNode) {

            const original =
                textNode.nodeValue || '';

            const normalized =
                original.replace(
                    /\s+/g,
                    ' '
                );

            const start =
                fullText.length;

            fullText += normalized;

            nodeData.push({
                node: textNode,
                original,
                normalized,
                start,
                end: fullText.length,
            });
        });


        const index =
            fullText.indexOf(
                normalizedSearch
            );


        if (index < 0) {
            return null;
        }


        const targetEnd =
            index + normalizedSearch.length;


        let startData = null;
        let endData = null;


        for (
            const data of nodeData
        ) {

            if (
                startData === null
                && index >= data.start
                && index <= data.end
            ) {
                startData = data;
            }


            if (
                targetEnd >= data.start
                && targetEnd <= data.end
            ) {
                endData = data;
                break;
            }
        }


        if (
            !startData
            || !endData
        ) {
            return null;
        }


        function normalizedOffsetToOriginal(
            data,
            normalizedOffset
        ) {

            if (
                normalizedOffset <= 0
            ) {
                return 0;
            }


            const original =
                data.original;


            let normalizedIndex = 0;


            for (
                let i = 0;
                i < original.length;
                i++
            ) {

                const char =
                    original[i];


                if (/\s/.test(char)) {

                    /*
                     * Semua rangkaian whitespace
                     * dihitung sebagai satu spasi.
                     */

                    if (
                        normalizedIndex <
                        data.normalized.length
                        && data.normalized[
                            normalizedIndex
                        ] === ' '
                    ) {

                        normalizedIndex++;
                    }


                    while (
                        i + 1 < original.length
                        && /\s/.test(
                            original[i + 1]
                        )
                    ) {
                        i++;
                    }

                } else {

                    normalizedIndex++;
                }


                if (
                    normalizedIndex >=
                    normalizedOffset
                ) {
                    return i + 1;
                }
            }


            return original.length;
        }


        const startNormalizedOffset =
            index
            - startData.start;


        const endNormalizedOffset =
            targetEnd
            - endData.start;


        const startOffset =
            normalizedOffsetToOriginal(
                startData,
                startNormalizedOffset
            );


        const endOffset =
            normalizedOffsetToOriginal(
                endData,
                endNormalizedOffset
            );


        const range =
            document.createRange();


        range.setStart(
            startData.node,
            Math.min(
                startOffset,
                startData.node.nodeValue.length
            )
        );


        range.setEnd(
            endData.node,
            Math.min(
                endOffset,
                endData.node.nodeValue.length
            )
        );


        return range;
    }


    function highlightRevision(
        comment
    ) {

        if (
            !isRevisionComment(comment)
            || isResolvedComment(comment)
        ) {
            return;
        }


        const searchText =
            String(
                comment.selected_text
                || ''
            ).trim();


        if (!searchText) {
            return;
        }


        const pages =
            getDocxPages();


        const pageNumber =
            Number(
                comment.page_number
            ) || 1;


        const page =
            pages[pageNumber - 1];


        if (!page) {
            return;
        }


        const range =
            findTextRange(
                page,
                searchText
            );


        if (!range) {
            return;
        }


        const span =
            document.createElement(
                'span'
            );


        span.className =
            'revision-mark';


        span.dataset.commentId =
            comment.id;


        span.title =
            'Klik untuk melihat revisi RPPM';


        try {

            range.surroundContents(
                span
            );

        } catch (error) {

            try {

                const fragment =
                    range.extractContents();


                span.appendChild(
                    fragment
                );


                range.insertNode(
                    span
                );

            } catch (fallbackError) {

                return;
            }
        }
    }


    function clearRevisionHighlights() {

        document
            .querySelectorAll(
                '#docx-preview .revision-mark'
            )
            .forEach(
                function (mark) {

                    const parent =
                        mark.parentNode;


                    if (!parent) {
                        return;
                    }


                    while (
                        mark.firstChild
                    ) {

                        parent.insertBefore(
                            mark.firstChild,
                            mark
                        );
                    }


                    mark.remove();
                }
            );
    }


    function applyAllRevisionHighlights() {

        clearRevisionHighlights();


        comments.forEach(
            highlightRevision
        );
    }


    document.addEventListener(
        'click',
        function (event) {

            const mark =
                event.target.closest(
                    '.revision-mark'
                );


            if (!mark) {
                return;
            }


            const id =
                mark.dataset.commentId;


            openCommentsPanel();


            const card =
                commentsBody?.querySelector(
                    `[data-comment-card="${id}"]`
                );


            card?.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });


            card?.animate(
                [
                    {
                        boxShadow:
                            '0 0 0 3px rgba(245,158,11,.25)'
                    },
                    {
                        boxShadow:
                            '0 0 0 0 rgba(245,158,11,0)'
                    }
                ],
                {
                    duration: 1000
                }
            );
        }
    );


    /* ================================================================
       COMMENTS PANEL
    ================================================================ */

    toggleCommentsButton?.addEventListener(
        'click',
        function () {

            workspace?.classList.toggle(
                'comments-open'
            );


            setTimeout(
                setupCommentComposerLayout,
                0
            );
        }
    );


    closeCommentsButton?.addEventListener(
        'click',
        closeCommentsPanel
    );


    document.addEventListener(
        'mousedown',
        function (event) {

            if (
                selectionToolbar
                && !selectionToolbar.contains(
                    event.target
                )
                && !docxPreview?.contains(
                    event.target
                )
            ) {

                clearSelectionToolbar();
            }
        }
    );


    /* ================================================================
       DOCX RENDER
    ================================================================ */

    function getDocxRenderer() {

        if (
            typeof docx !== 'undefined'
            && typeof docx.renderAsync === 'function'
        ) {
            return docx;
        }


        if (
            typeof window.docx !== 'undefined'
            && typeof window.docx.renderAsync === 'function'
        ) {
            return window.docx;
        }


        return null;
    }


    function makePageDock() {

        if (
            !pageList
            || !docxPreview
        ) {
            return;
        }


        pageList.innerHTML = '';


        const pages =
            getDocxPages();


        if (pageCount) {
            pageCount.textContent =
                pages.length;
        }


        pages.forEach(
            function (page, index) {

                const button =
                    document.createElement(
                        'button'
                    );


                button.type =
                    'button';


                button.className =
                    'page-tab';


                button.innerHTML = `
                    <span class="page-number">
                        Halaman ${index + 1}
                    </span>

                    <div class="page-thumb">

                        <div class="page-thumb-inner"></div>

                    </div>
                `;


                const thumb =
                    button.querySelector(
                        '.page-thumb-inner'
                    );


                const clone =
                    page.cloneNode(true);


                clone
                    .querySelectorAll(
                        '.docx'
                    )
                    .forEach(
                        function (nested) {

                            nested
                                .classList
                                .remove('docx');
                        }
                    );


                thumb.appendChild(
                    clone
                );


                button.addEventListener(
                    'click',
                    function () {

                        pages[index]
                            .scrollIntoView({
                                behavior: 'smooth',
                                block: 'start',
                            });


                        pageList
                            .querySelectorAll(
                                '.page-tab'
                            )
                            .forEach(
                                item =>
                                    item.classList.remove(
                                        'active'
                                    )
                            );


                        button.classList.add(
                            'active'
                        );
                    }
                );


                pageList.appendChild(
                    button
                );
            }
        );


        if (pages.length) {

            pageList
                .querySelector(
                    '.page-tab'
                )
                ?.classList.add(
                    'active'
                );
        }
    }


    async function renderDocx(
        arrayBuffer
    ) {

        const renderer =
            getDocxRenderer();


        if (!renderer) {

            throw new Error(
                'Library DOCX Preview belum tersedia.'
            );
        }


        docxPreview.innerHTML =
            '';


        await renderer.renderAsync(
            arrayBuffer,
            docxPreview,
            null,
            {
                className: 'docx',
                inWrapper: true,
                breakPages: true,
                ignoreWidth: false,
                ignoreHeight: false,
                ignoreFonts: false,
                useBase64URL: true,
                renderHeaders: true,
                renderFooters: true,
                renderFootnotes: true,
                renderEndnotes: true,
            }
        );


        /*
         * Pastikan setiap halaman hasil render DOCX berdiri sendiri.
         * docx-preview membuat satu .docx untuk setiap halaman ketika
         * breakPages aktif.
         */
        const renderedPages =
            getDocxPages();

        renderedPages.forEach(function (page, index) {
            page.dataset.pageNumber = String(index + 1);
            page.style.display = 'block';
            page.style.position = 'relative';
            page.style.margin = '0 auto 24px';
            page.style.boxSizing = 'border-box';
            page.style.breakAfter =
                index < renderedPages.length - 1
                    ? 'page'
                    : 'auto';
        });

        docxPreview.contentEditable =
            isGuru ? 'true' : 'false';


        docxStage.style.display =
            'block';


        emptyState.style.display =
            'none';


        fileInfoBar.style.display =
            'flex';


        makePageDock();

        /* Render/layout selesai secara async; refresh daftar halaman sekali lagi. */
        requestAnimationFrame(function () {
            makePageDock();
        });

        setTimeout(function () {
            makePageDock();
            applyAllRevisionHighlights();
        }, 250);
    }


    async function loadExistingDocx() {

        if (!existingFileUrl) {
            return false;
        }


        showLoading(
            'Membuka RPPM...',
            'Dokumen RPPM sedang diproses.'
        );


        try {

            const response =
                await fetch(
                    existingFileUrl,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/octet-stream',
                        },
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Dokumen RPPM tidak dapat dimuat.'
                );
            }


            const buffer =
                await response.arrayBuffer();


            await renderDocx(
                buffer
            );


            if (fileStatus) {

                fileStatus.textContent =
                    'Dokumen RPPM tersimpan';
            }


            return true;

        } catch (error) {

            showToast(
                'Gagal membuka RPPM',
                error.message
                    || 'Dokumen RPPM tidak dapat dibuka.',
                'error'
            );


            return false;

        } finally {

            hideLoading();
        }
    }


    async function initializeDocument() {

        if (!existingDocumentId) {

            docxStage.style.display =
                'none';

            emptyState.style.display =
                'flex';

            return;
        }


        if (
            existingContent
            && String(existingContent).trim()
        ) {

            docxStage.style.display =
                'block';

            emptyState.style.display =
                'none';

            fileInfoBar.style.display =
                'flex';


            if (
                !docxPreview.querySelector(
                    '.docx'
                )
            ) {

                docxPreview.innerHTML =
                    existingContent;
            }


            makePageDock();


            setTimeout(
                applyAllRevisionHighlights,
                350
            );


            return;
        }


        await loadExistingDocx();
    }


    /* ================================================================
       ACADEMIC DRIVE
    ================================================================ */

    function fillAcademicDropdown(select, items, emptyText) {
        if (!select) return;

        select.innerHTML = '';

        const first = document.createElement('option');
        first.value = '';
        first.textContent = emptyText;
        select.appendChild(first);

        (items || []).slice(0, 4).forEach(function (item) {
            const option = document.createElement('option');
            option.value = item.view_url || '';
            option.textContent = item.title || item.original_filename || 'Dokumen';
            select.appendChild(option);
        });
    }


    async function loadAcademicDrive() {
        if (!academicBrowseRoute) return;

        try {
            const response = await fetch(academicBrowseRoute, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const raw = await response.text();

            if (!response.ok) {
                console.error('Academic Drive response:', response.status, raw);
                throw new Error('Gagal memuat dokumen.');
            }

            let data;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                console.error('Academic Drive bukan JSON:', raw);
                throw new Error('Gagal memuat dokumen.');
            }

            const items = Array.isArray(data.data)
                ? data.data
                : (Array.isArray(data) ? data : []);

            const savedItems = items.filter(item =>
                item.is_saved === true ||
                item.is_saved === 1 ||
                item.is_saved === '1' ||
                item.status === 'saved'
            );

            fillAcademicDropdown(
                teacherDocumentsDropdown,
                items.slice(0, 4),
                'Dokumen Guru'
            );

            fillAcademicDropdown(
                archiveDocumentsDropdown,
                savedItems.slice(0, 4),
                'Arsip Tersimpan'
            );
        } catch (error) {
            console.error(error);
            if (archiveDocumentsDropdown) {
                archiveDocumentsDropdown.innerHTML = '<option value="">Arsip Tersimpan</option>';
            }
        }
    }


    teacherDocumentsDropdown?.addEventListener('change', function () {
        if (this.value) window.location.href = this.value;
        this.selectedIndex = 0;
    });


    archiveDocumentsDropdown?.addEventListener('change', function () {
        if (this.value) window.location.href = this.value;
        this.selectedIndex = 0;
    });


    saveArchiveButton?.addEventListener('click', async function () {
        if (!academicSaveArchiveRoute) {
            showToast('Tidak tersedia', 'Route Simpan ke Drive belum tersedia.', 'error');
            return;
        }

        const title = window.prompt('Judul dokumen RPPM:', existingFileName || 'RPPM');
        if (title === null || !title.trim()) return;

        try {
            const response = await fetch(academicSaveArchiveRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    title: title.trim(),
                    publish: true,
                    content: docxPreview?.innerHTML || '',
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Gagal menyimpan ke Drive.');

            showToast('Berhasil', data.message || 'RPPM berhasil disimpan ke Drive.', 'success');
            loadAcademicDrive();
        } catch (error) {
            showToast('Gagal', error.message || 'RPPM gagal disimpan ke Drive.', 'error');
        }
    });


    loadAcademicDrive();


    /* ================================================================
       UPLOAD RPPM
    ================================================================ */

    async function uploadDocument(
        file
    ) {

        if (!isGuru) {

            showToast(
                'Akses ditolak',
                'Hanya Guru yang dapat mengunggah RPPM.',
                'error'
            );

            return;
        }


        if (!file) {
            return;
        }


        if (
            !file.name
                .toLowerCase()
                .endsWith('.docx')
        ) {

            showToast(
                'File tidak valid',
                'Silakan pilih file RPPM dalam format DOCX.',
                'error'
            );

            return;
        }


        if (
            file.size
            > 20 * 1024 * 1024
        ) {

            showToast(
                'File terlalu besar',
                'Ukuran maksimal file adalah 20 MB.',
                'error'
            );

            return;
        }


        if (!uploadRoute) {

            showToast(
                'Upload tidak tersedia',
                'Route upload RPPM belum tersedia.',
                'error'
            );

            return;
        }


        const formData =
            new FormData();


        formData.append(
            'file',
            file
        );


        showLoading(
            'Mengunggah RPPM...',
            'Dokumen RPPM sedang diproses. Mohon tunggu.'
        );


        try {

            const response =
                await fetch(
                    uploadRoute,
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'Accept':
                                'application/json',
                        },

                        body:
                            formData,
                    }
                );


            const data =
                await response
                    .json()
                    .catch(() => ({}));


            if (!response.ok) {

                throw new Error(
                    data.message
                    || 'Upload RPPM gagal.'
                );
            }


            if (data.redirect) {

                window.location.href =
                    data.redirect;

                return;
            }


            if (data.url) {

                window.location.href =
                    data.url;

                return;
            }


            if (
                data.document_id
                && editRouteTemplate
            ) {

                window.location.href =
                    editRouteTemplate.replace(
                        '__DOCUMENT__',
                        encodeURIComponent(
                            data.document_id
                        )
                    );

                return;
            }


            showToast(
                'RPPM berhasil diunggah',
                'Dokumen RPPM berhasil diunggah.',
                'success'
            );

        } catch (error) {

            showToast(
                'Upload RPPM gagal',
                error.message
                    || 'Dokumen RPPM gagal diunggah.',
                'error'
            );

        } finally {

            hideLoading();

            if (wordFile) {
                wordFile.value = '';
            }
        }
    }


    uploadButton?.addEventListener(
        'click',
        function () {
            wordFile?.click();
        }
    );


    uploadMainButton?.addEventListener(
        'click',
        function () {
            wordFile?.click();
        }
    );


    wordFile?.addEventListener(
        'change',
        function () {

            const file =
                this.files?.[0];

            uploadDocument(
                file
            );
        }
    );


    dropZone?.addEventListener(
        'click',
        function () {

            wordFile?.click();
        }
    );


    dropZone?.addEventListener(
        'dragover',
        function (event) {

            event.preventDefault();

            dropZone.classList.add(
                'dragover'
            );
        }
    );


    dropZone?.addEventListener(
        'dragleave',
        function () {

            dropZone.classList.remove(
                'dragover'
            );
        }
    );


    dropZone?.addEventListener(
        'drop',
        function (event) {

            event.preventDefault();

            dropZone.classList.remove(
                'dragover'
            );


            const file =
                event.dataTransfer
                    ?.files?.[0];


            uploadDocument(
                file
            );
        }
    );


    /* ================================================================
       AUTOSAVE RPPM
    ================================================================ */

    async function saveDocumentAuto() {

        if (!isGuru) {
            return;
        }


        if (!saveRoute) {
            return;
        }


        if (!docxPreview) {
            return;
        }


        if (isSaving) {
            return;
        }


        isSaving = true;


        try {

            const response =
                await fetch(
                    saveRoute,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        body:
                            JSON.stringify({
                                content:
                                    docxPreview.innerHTML,

                                title:
                                    existingFileName,
                            }),
                    }
                );


            const data =
                await response
                    .json()
                    .catch(() => ({}));


            if (!response.ok) {

                throw new Error(
                    data.message
                    || 'Autosave RPPM gagal.'
                );
            }


            showSaveStatus(
                'Tersimpan',
                'success'
            );


            if (fileStatus) {

                fileStatus.textContent =
                    data.saved_at
                        ? `RPPM tersimpan ${data.saved_at}`
                        : 'Dokumen RPPM tersimpan';
            }

        } catch (error) {

            showSaveStatus(
                'Gagal menyimpan',
                'error'
            );

        } finally {

            isSaving = false;
        }
    }


    function scheduleAutoSave() {

        if (!isGuru) {
            return;
        }


        clearTimeout(
            autoSaveTimer
        );


        showSaveStatus(
            'Menyimpan...',
            'success'
        );


        autoSaveTimer =
            setTimeout(
                saveDocumentAuto,
                1200
            );
    }


    if (isGuru) {

        docxPreview?.addEventListener(
            'input',
            scheduleAutoSave
        );


        docxPreview?.addEventListener(
            'keyup',
            scheduleAutoSave
        );


        docxPreview?.addEventListener(
            'paste',
            function () {

                setTimeout(
                    scheduleAutoSave,
                    100
                );
            }
        );
    }


    /* ================================================================
       EDITOR COMMAND
    ================================================================ */

    document
        .querySelectorAll(
            '.command-button'
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        if (!isGuru) {
                            return;
                        }


                        const command =
                            button.dataset.command;


                        if (!command) {
                            return;
                        }


                        docxPreview?.focus();


                        document.execCommand(
                            command,
                            false,
                            null
                        );


                        scheduleAutoSave();
                    }
                );
            }
        );


    fontName?.addEventListener(
        'change',
        function () {

            if (!isGuru) {
                return;
            }


            docxPreview?.focus();


            document.execCommand(
                'fontName',
                false,
                this.value
            );


            scheduleAutoSave();
        }
    );


    fontSize?.addEventListener(
        'change',
        function () {

            if (!isGuru) {
                return;
            }


            docxPreview?.focus();


            document.execCommand(
                'fontSize',
                false,
                this.value
            );


            scheduleAutoSave();
        }
    );


    /* ================================================================
       ZOOM
    ================================================================ */

    function applyZoom() {

        if (!docxStage) {
            return;
        }


        docxStage.style.transform =
            `scale(${zoom})`;


        docxStage.style.transformOrigin =
            'top center';


        if (zoomValue) {

            zoomValue.textContent =
                Math.round(
                    zoom * 100
                ) + '%';
        }
    }


    zoomOut?.addEventListener(
        'click',
        function () {

            zoom =
                Math.max(
                    .5,
                    zoom - .1
                );


            applyZoom();
        }
    );


    zoomIn?.addEventListener(
        'click',
        function () {

            zoom =
                Math.min(
                    2,
                    zoom + .1
                );


            applyZoom();
        }
    );


    /* ================================================================
       FULLSCREEN
    ================================================================ */

    fullscreenButton?.addEventListener(
        'click',
        async function () {

            try {

                if (!document.fullscreenElement) {

                    await document
                        .documentElement
                        .requestFullscreen();

                } else {

                    await document
                        .exitFullscreen();
                }

            } catch (error) {

                showToast(
                    'Fullscreen',
                    'Mode fullscreen tidak tersedia di browser ini.',
                    'error'
                );
            }
        }
    );


    /* ================================================================
       PRINT
    ================================================================ */

    printButton?.addEventListener(
        'click',
        function () {

            window.print();
        }
    );


    /* ================================================================
       DELETE RPPM
    ================================================================ */

    clearButton?.addEventListener(
        'click',
        async function () {

            if (!deleteRoute) {

                showToast(
                    'Tidak tersedia',
                    'Route hapus RPPM belum tersedia.',
                    'error'
                );

                return;
            }


            if (
                !confirm(
                    'Apakah Anda yakin ingin menghapus dokumen RPPM ini?'
                )
            ) {
                return;
            }


            showLoading(
                'Menghapus RPPM...',
                'Dokumen RPPM sedang dihapus.'
            );


            try {

                const response =
                    await fetch(
                        deleteRoute,
                        {
                            method: 'DELETE',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );


                const data =
                    await response
                        .json()
                        .catch(() => ({}));


                if (!response.ok) {

                    throw new Error(
                        data.message
                        || 'Dokumen RPPM gagal dihapus.'
                    );
                }


                showToast(
                    'RPPM dihapus',
                    'Dokumen RPPM berhasil dihapus.',
                    'success'
                );


                setTimeout(
                    function () {

                        if (data.redirect) {

                            window.location.href =
                                data.redirect;

                        } else {

                            window.location.reload();

                        }
                    },
                    700
                );

            } catch (error) {

                showToast(
                    'Gagal menghapus RPPM',
                    error.message
                        || 'Dokumen RPPM gagal dihapus.',
                    'error'
                );

            } finally {

                hideLoading();
            }
        }
    );


    /* ================================================================
       CTRL + S
    ================================================================ */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                (event.ctrlKey || event.metaKey)
                && event.key.toLowerCase() === 's'
            ) {

                event.preventDefault();


                if (isGuru) {

                    saveDocumentAuto();
                }
            }
        }
    );


    /* ================================================================
       RESIZE
    ================================================================ */

    window.addEventListener(
        'resize',
        function () {

            clearSelectionToolbar();

            setupCommentComposerLayout();
        }
    );


    /* ================================================================
       START
    ================================================================ */

    rerenderComments();

    setupCommentComposerLayout();


    if (commentComposer) {

        commentComposer.classList.add(
            'show'
        );

        commentComposer.style.display =
            'block';
    }


    if (commentsBody) {

        commentsBody.style.minHeight =
            '0';

        commentsBody.style.overflowY =
            'auto';
    }


    applyZoom();


    initializeDocument();

});
</script>

</body>
</html>