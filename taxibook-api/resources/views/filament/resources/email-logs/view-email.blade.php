<div class="space-y-4">
    <div>
        <h3 class="text-sm font-medium text-gray-500">Status</h3>
        <p class="mt-1 text-sm">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $record->status === 'sent' ? 'bg-green-100 text-green-800' : '' }}
                {{ $record->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $record->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                {{ ucfirst($record->status) }}
            </span>
        </p>
    </div>

    <div>
        <h3 class="text-sm font-medium text-gray-500">Recipients</h3>
        <p class="mt-1 text-sm">
            <strong>To:</strong> {{ $record->recipient_email }} ({{ $record->recipient_name }})<br>
            @if($record->cc_emails)
                <strong>CC:</strong> {{ $record->cc_emails }}<br>
            @endif
            @if($record->bcc_emails)
                <strong>BCC:</strong> {{ $record->bcc_emails }}<br>
            @endif
        </p>
    </div>

    <div>
        <h3 class="text-sm font-medium text-gray-500">Subject</h3>
        <p class="mt-1 text-sm">{{ $record->subject }}</p>
    </div>

    <div>
        <h3 class="text-sm font-medium text-gray-500">Email Body</h3>
        <div class="mt-1 p-4 bg-gray-50 rounded-md max-h-96 overflow-y-auto">
            {!! $record->body !!}
        </div>
    </div>

    @if($record->error_message)
    <div>
        <h3 class="text-sm font-medium text-red-500">Error Message</h3>
        <p class="mt-1 text-sm text-red-600">{{ $record->error_message }}</p>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Queued At</h3>
            <p class="mt-1 text-sm">{{ $record->created_at->format('M j, Y g:i A') }}</p>
        </div>
        @if($record->sent_at)
        <div>
            <h3 class="text-sm font-medium text-gray-500">Sent At</h3>
            <p class="mt-1 text-sm">{{ $record->sent_at->format('M j, Y g:i A') }}</p>
        </div>
        @endif
    </div>

    @if($record->opened_at)
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">First Opened</h3>
            <p class="mt-1 text-sm">{{ $record->opened_at->format('M j, Y g:i A') }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Open Count</h3>
            <p class="mt-1 text-sm">{{ $record->open_count }} times</p>
        </div>
    </div>
    @endif
</div>