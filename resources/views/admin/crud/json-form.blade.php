<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JSON Editor Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Ace Editor CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.3/ace.js" integrity="sha512-" crossorigin="anonymous"></script>

    <style>
        #editor {
            height: 500px;
            width: 100%;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .status-box {
            margin: 10px 0;
            padding: 10px;
            display: none;
            border-radius: 4px;
        }
        .status-success {
            background-color: #e6ffed;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .status-error {
            background-color: #ffe6e6;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
    </style>
</head>
<body>

    <h2>Paste Your JSON Configuration</h2>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <!-- JSON Validator Status Message -->
    <div id="jsonStatus" class="status-box"></div>

    <form id="jsonForm" action="{{ route('json.form.submit') }}" method="POST">
        @csrf

        <!-- Ace Editor -->
        <div id="editor">{{ old('crud_json', $json) }}</div>

        <!-- Hidden Textarea -->
        <textarea name="crud_json" id="crud_json" style="display: none;"></textarea>

        <br>
        <button type="button" onclick="checkJsonSyntax()">Check JSON Syntax</button>
        <button type="submit">Submit JSON</button>
    </form>

    <script>
        const editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/json");
        editor.session.setUseWorker(false); // disable Ace’s own linting

        const jsonStatus = document.getElementById('jsonStatus');

        function checkJsonSyntax() {
            const code = editor.getValue();
            try {
                JSON.parse(code);
                jsonStatus.textContent = "✅ JSON is valid.";
                jsonStatus.className = "status-box status-success";
                jsonStatus.style.display = "block";
            } catch (e) {
                const match = e.message.match(/position (\d+)/);
                let line = '?';
                if (match) {
                    const pos = parseInt(match[1]);
                    const lines = code.substring(0, pos).split('\n');
                    line = lines.length;
                }
                jsonStatus.textContent = `❌ JSON Error on line ${line}: ${e.message}`;
                jsonStatus.className = "status-box status-error";
                jsonStatus.style.display = "block";
            }
        }

        // On form submit, copy editor content to textarea
        document.getElementById('jsonForm').addEventListener('submit', function () {
            document.getElementById('crud_json').value = editor.getValue();
        });
    </script>

</body>
</html>
