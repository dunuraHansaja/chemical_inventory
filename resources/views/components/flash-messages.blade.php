<div class="toast-container" data-toast-container aria-label="Notifications">
    @if (session('success'))
        <div class="toast toast-success" role="status">
            <span class="toast-icon" aria-hidden="true">✓</span>
            <div class="toast-copy"><strong>{{ session('success_title', 'Success') }}</strong><span>{{ session('success') }}</span></div>
            <button class="toast-close" type="button" aria-label="Dismiss notification">×</button>
        </div>
    @endif
    @if (session('info'))
        <div class="toast toast-info" role="status">
            <span class="toast-icon" aria-hidden="true">i</span>
            <div class="toast-copy"><strong>Information</strong><span>{{ session('info') }}</span></div>
            <button class="toast-close" type="button" aria-label="Dismiss notification">×</button>
        </div>
    @endif
    @if (session('warning'))
        <div class="toast toast-warning" role="status" data-toast-persistent>
            <span class="toast-icon" aria-hidden="true">!</span>
            <div class="toast-copy"><strong>Warning</strong><span>{{ session('warning') }}</span></div>
            <button class="toast-close" type="button" aria-label="Dismiss notification">×</button>
        </div>
    @endif
    @if (session('error'))
        <div class="toast toast-error" role="alert" data-toast-persistent>
            <span class="toast-icon" aria-hidden="true">×</span>
            <div class="toast-copy"><strong>Error</strong><span>{{ session('error') }}</span></div>
            <button class="toast-close" type="button" aria-label="Dismiss notification">×</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="toast toast-error" role="alert" data-toast-persistent>
            <span class="toast-icon" aria-hidden="true">×</span>
            <div class="toast-copy">
                <strong>Please correct the following issues</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            <button class="toast-close" type="button" aria-label="Dismiss notification">×</button>
        </div>
    @endif
</div>
