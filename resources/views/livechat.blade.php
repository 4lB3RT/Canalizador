<!DOCTYPE html>
<html>
<head>
    <title>Live Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="card">
        <div class="card-header">Live Chat</div>
        <div class="card-body" id="chat-box" style="height:300px; overflow-y:auto;"></div>
        <div class="card-footer">
            <form id="chat-form">
                <div class="input-group">
                    <input type="text" id="user-input" class="form-control" placeholder="Type your message...">
                    <button class="btn btn-primary" type="submit">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const chatBox = document.getElementById('chat-box');
    document.getElementById('chat-form').onsubmit = async function(e) {
        e.preventDefault();
        const input = document.getElementById('user-input');
        const message = input.value;
        chatBox.innerHTML += `<div class="mb-2 text-end"><strong>You:</strong> ${message}</div>`;
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        await fetch("{{ url('/livechat/send') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message })
        });
    };

    // Listen for agent responses via SSE
    const evtSource = new EventSource("{{ url('/livechat/stream') }}");
    evtSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        chatBox.innerHTML += `<div class="mb-2"><strong>Agent:</strong> ${data.message}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;
    };
</script>
</body>
</html>
