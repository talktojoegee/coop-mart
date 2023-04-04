@php
    $messages = '';
    $messageCount = 0;
    if (!empty(Auth::user()->id)) {
        $contacts = Modules\Ticket\Http\Models\Chat::getMyContactListWithLastMessage();
        $messageCount = Modules\Ticket\Http\Models\Message::totalUnreadMessages();
    }
@endphp
<div class="chat-parent-container">
    <div class="chat-toggle-container">
        <div class="chat-toggle-button">
            <svg class="chat-message-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 19" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M7.94606 4.0182e-07H10.0539C11.4126 -1.49762e-05 12.5083 -2.74926e-05 13.3874 0.0894005C14.2948 0.181709 15.0817 0.377504 15.7779 0.842653C16.3238 1.20745 16.7926 1.6762 17.1573 2.22215C17.6225 2.91829 17.8183 3.70523 17.9106 4.61264C18 5.49173 18 6.58738 18 7.94604V8.05396C18 9.41262 18 10.5083 17.9106 11.3874C17.8183 12.2948 17.6225 13.0817 17.1573 13.7778C16.7926 14.3238 16.3238 14.7926 15.7778 15.1573C15.1699 15.5636 14.4931 15.7642 13.7267 15.8701C13.1247 15.9534 12.4279 15.9827 11.6213 15.9935L10.7889 17.6584C10.0518 19.1325 7.94819 19.1325 7.21115 17.6584L6.37872 15.9935C5.57211 15.9827 4.87525 15.9534 4.2733 15.8701C3.50685 15.7642 2.83014 15.5636 2.22215 15.1573C1.6762 14.7926 1.20745 14.3238 0.842653 13.7778C0.377504 13.0817 0.181709 12.2948 0.0894005 11.3874C-2.74926e-05 10.5083 -1.49762e-05 9.41261 4.0182e-07 8.05394V7.94606C-1.49762e-05 6.58739 -2.74926e-05 5.49174 0.0894005 4.61264C0.181709 3.70523 0.377504 2.91829 0.842653 2.22215C1.20745 1.6762 1.6762 1.20745 2.22215 0.842653C2.91829 0.377504 3.70523 0.181709 4.61264 0.0894005C5.49174 -2.74926e-05 6.58739 -1.49762e-05 7.94606 4.0182e-07ZM4.81505 2.07913C4.06578 2.15535 3.64604 2.29662 3.33329 2.50559C3.00572 2.72447 2.72447 3.00572 2.50559 3.33329C2.29662 3.64604 2.15535 4.06578 2.07913 4.81505C2.00121 5.58104 2 6.57472 2 8C2 9.42527 2.00121 10.419 2.07913 11.1849C2.15535 11.9342 2.29662 12.354 2.50559 12.6667C2.72447 12.9943 3.00572 13.2755 3.33329 13.4944C3.60665 13.6771 3.96223 13.8081 4.54716 13.889C5.14815 13.9721 5.92075 13.9939 7.00436 13.9986C7.40885 14.0004 7.75638 14.2421 7.91233 14.5886L9 16.7639L10.0877 14.5886C10.2436 14.2421 10.5912 14.0004 10.9956 13.9986C12.0792 13.9939 12.8518 13.9721 13.4528 13.889C14.0378 13.8081 14.3933 13.6771 14.6667 13.4944C14.9943 13.2755 15.2755 12.9943 15.4944 12.6667C15.7034 12.354 15.8446 11.9342 15.9209 11.1849C15.9988 10.419 16 9.42527 16 8C16 6.57472 15.9988 5.58104 15.9209 4.81505C15.8446 4.06578 15.7034 3.64604 15.4944 3.33329C15.2755 3.00572 14.9943 2.72447 14.6667 2.50559C14.354 2.29662 13.9342 2.15535 13.1849 2.07913C12.419 2.00121 11.4253 2 10 2H8C6.57473 2 5.58104 2.00121 4.81505 2.07913Z"
                    fill="#2C2C2C"></path>
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M5 6C5 5.44772 5.44772 5 6 5L12 5C12.5523 5 13 5.44772 13 6C13 6.55228 12.5523 7 12 7L6 7C5.44772 7 5 6.55228 5 6Z"
                    fill="#2C2C2C"></path>
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M5 10C5 9.44772 5.44772 9 6 9H9C9.55228 9 10 9.44772 10 10C10 10.5523 9.55228 11 9 11H6C5.44772 11 5 10.5523 5 10Z"
                    fill="#2C2C2C"></path>
            </svg>
            <span>{{ __('Messages') }}</span>
            @auth
                <span class="chat-unread-count {{ $messageCount > 0 ? 'flex' : 'none' }}">{{ $messageCount }}</span>
            @endauth
        </div>
    </div>
    <div class="chat-view-container chat-hidden" data-refreshurl="{{ route('chat.inbox-refresh') }}">
        <div class="chat-view-header">
            <div class="chat-view-header-text">
                <svg class="chat-message-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 19" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M7.94606 4.0182e-07H10.0539C11.4126 -1.49762e-05 12.5083 -2.74926e-05 13.3874 0.0894005C14.2948 0.181709 15.0817 0.377504 15.7779 0.842653C16.3238 1.20745 16.7926 1.6762 17.1573 2.22215C17.6225 2.91829 17.8183 3.70523 17.9106 4.61264C18 5.49173 18 6.58738 18 7.94604V8.05396C18 9.41262 18 10.5083 17.9106 11.3874C17.8183 12.2948 17.6225 13.0817 17.1573 13.7778C16.7926 14.3238 16.3238 14.7926 15.7778 15.1573C15.1699 15.5636 14.4931 15.7642 13.7267 15.8701C13.1247 15.9534 12.4279 15.9827 11.6213 15.9935L10.7889 17.6584C10.0518 19.1325 7.94819 19.1325 7.21115 17.6584L6.37872 15.9935C5.57211 15.9827 4.87525 15.9534 4.2733 15.8701C3.50685 15.7642 2.83014 15.5636 2.22215 15.1573C1.6762 14.7926 1.20745 14.3238 0.842653 13.7778C0.377504 13.0817 0.181709 12.2948 0.0894005 11.3874C-2.74926e-05 10.5083 -1.49762e-05 9.41261 4.0182e-07 8.05394V7.94606C-1.49762e-05 6.58739 -2.74926e-05 5.49174 0.0894005 4.61264C0.181709 3.70523 0.377504 2.91829 0.842653 2.22215C1.20745 1.6762 1.6762 1.20745 2.22215 0.842653C2.91829 0.377504 3.70523 0.181709 4.61264 0.0894005C5.49174 -2.74926e-05 6.58739 -1.49762e-05 7.94606 4.0182e-07ZM4.81505 2.07913C4.06578 2.15535 3.64604 2.29662 3.33329 2.50559C3.00572 2.72447 2.72447 3.00572 2.50559 3.33329C2.29662 3.64604 2.15535 4.06578 2.07913 4.81505C2.00121 5.58104 2 6.57472 2 8C2 9.42527 2.00121 10.419 2.07913 11.1849C2.15535 11.9342 2.29662 12.354 2.50559 12.6667C2.72447 12.9943 3.00572 13.2755 3.33329 13.4944C3.60665 13.6771 3.96223 13.8081 4.54716 13.889C5.14815 13.9721 5.92075 13.9939 7.00436 13.9986C7.40885 14.0004 7.75638 14.2421 7.91233 14.5886L9 16.7639L10.0877 14.5886C10.2436 14.2421 10.5912 14.0004 10.9956 13.9986C12.0792 13.9939 12.8518 13.9721 13.4528 13.889C14.0378 13.8081 14.3933 13.6771 14.6667 13.4944C14.9943 13.2755 15.2755 12.9943 15.4944 12.6667C15.7034 12.354 15.8446 11.9342 15.9209 11.1849C15.9988 10.419 16 9.42527 16 8C16 6.57472 15.9988 5.58104 15.9209 4.81505C15.8446 4.06578 15.7034 3.64604 15.4944 3.33329C15.2755 3.00572 14.9943 2.72447 14.6667 2.50559C14.354 2.29662 13.9342 2.15535 13.1849 2.07913C12.419 2.00121 11.4253 2 10 2H8C6.57473 2 5.58104 2.00121 4.81505 2.07913Z"
                        fill="#2C2C2C"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M5 6C5 5.44772 5.44772 5 6 5L12 5C12.5523 5 13 5.44772 13 6C13 6.55228 12.5523 7 12 7L6 7C5.44772 7 5 6.55228 5 6Z"
                        fill="#2C2C2C"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M5 10C5 9.44772 5.44772 9 6 9H9C9.55228 9 10 9.44772 10 10C10 10.5523 9.55228 11 9 11H6C5.44772 11 5 10.5523 5 10Z"
                        fill="#2C2C2C"></path>
                </svg>
                <span>{{ __('Messages') }}</span>
            </div>
            <div class="chat-view-close-button">
                <span class="chat-message-icon m-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11"
                        fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M0.402728 0.402728C0.939699 -0.134243 1.8103 -0.134243 2.34727 0.402728L10.5973 8.65273C11.1342 9.1897 11.1342 10.0603 10.5973 10.5973C10.0603 11.1342 9.1897 11.1342 8.65273 10.5973L0.402728 2.34727C-0.134243 1.8103 -0.134243 0.939699 0.402728 0.402728Z"
                            fill="#2C2C2C"></path>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M10.5973 0.402728C10.0603 -0.134243 9.1897 -0.134243 8.65273 0.402728L0.402728 8.65273C-0.134243 9.1897 -0.134243 10.0603 0.402728 10.5973C0.939699 11.1342 1.8103 11.1342 2.34727 10.5973L10.5973 2.34727C11.1342 1.8103 11.1342 0.939699 10.5973 0.402728Z"
                            fill="#2C2C2C"></path>
                    </svg>
                </span>
            </div>
        </div>
        <div class="chat-view-body">
            @include('ticket::pieces.sidebar')
            @include('ticket::pieces.chat-history')
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ Module::asset('ticket:css/chat-widget.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('public/dist/js/xss.min.js') }}"></script>
    <script src="{{ Module::asset('ticket:js/chat.min.js') }}"></script>
@endpush
