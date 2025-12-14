<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel + Ollama AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Laravel + Ollama AI</h1>
            <p class="text-gray-600 mb-8">Interact with AI models powered by Ollama</p>

            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Ollama Status</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            @if($isOllamaAvailable)
                                <span class="inline-flex items-center">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    Connected
                                </span>
                            @else
                                <span class="inline-flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                    Disconnected
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($isOllamaAvailable && count($models) > 0)
                        <div>
                            <p class="text-sm text-gray-600">Available Models: {{ count($models) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if(!$isOllamaAvailable)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Ollama service is not available. Make sure it's running and accessible at the configured URL.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button onclick="showTab('query')" id="tab-query" class="tab-button active px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                            Simple Query
                        </button>
                        <button onclick="showTab('code')" id="tab-code" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Explain Code
                        </button>
                        <button onclick="showTab('summarize')" id="tab-summarize" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Summarize
                        </button>
                        <button onclick="showTab('ask')" id="tab-ask" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Q&A
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Simple Query Tab -->
                    <div id="content-query" class="tab-content">
                        <h3 class="text-lg font-semibold mb-4">Ask AI Anything</h3>
                        <form onsubmit="submitQuery(event)" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Question</label>
                                <textarea id="query-prompt" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="What is Laravel?"></textarea>
                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-medium">
                                Submit
                            </button>
                        </form>
                        <div id="query-result" class="mt-4 hidden">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Response:</h4>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <p id="query-response" class="text-gray-800 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div id="query-loading" class="mt-4 hidden">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                <span class="ml-2 text-gray-600">Processing...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Explain Code Tab -->
                    <div id="content-code" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Explain Code</h3>
                        <form onsubmit="submitCodeExplanation(event)" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Code to Explain</label>
                                <textarea id="code-input" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="function hello() {&#10;    return 'Hello World';&#10;}"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                <select id="code-language" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="php">PHP</option>
                                    <option value="javascript">JavaScript</option>
                                    <option value="python">Python</option>
                                    <option value="java">Java</option>
                                </select>
                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-medium">
                                Explain
                            </button>
                        </form>
                        <div id="code-result" class="mt-4 hidden">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Explanation:</h4>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <p id="code-response" class="text-gray-800 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div id="code-loading" class="mt-4 hidden">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                <span class="ml-2 text-gray-600">Analyzing code...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Summarize Tab -->
                    <div id="content-summarize" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Summarize Text</h3>
                        <form onsubmit="submitSummarize(event)" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Text to Summarize</label>
                                <textarea id="summarize-text" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Paste a long text here to get a summary..."></textarea>
                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-medium">
                                Summarize
                            </button>
                        </form>
                        <div id="summarize-result" class="mt-4 hidden">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Summary:</h4>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <p id="summarize-response" class="text-gray-800 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div id="summarize-loading" class="mt-4 hidden">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                <span class="ml-2 text-gray-600">Summarizing...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Q&A Tab -->
                    <div id="content-ask" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Question & Answer</h3>
                        <form onsubmit="submitQuestion(event)" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Context (Optional)</label>
                                <textarea id="ask-context" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Provide context for better answers..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Question</label>
                                <input type="text" id="ask-question" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="What is the main point?">
                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-medium">
                                Ask
                            </button>
                        </form>
                        <div id="ask-result" class="mt-4 hidden">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Answer:</h4>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <p id="ask-response" class="text-gray-800 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div id="ask-loading" class="mt-4 hidden">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                <span class="ml-2 text-gray-600">Thinking...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active class to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.add('active', 'border-blue-500', 'text-blue-600');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
        }

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Simple Query
        async function submitQuery(event) {
            event.preventDefault();
            const prompt = document.getElementById('query-prompt').value;

            if (!prompt.trim()) return;

            showLoading('query');
            hideResult('query');

            try {
                const response = await fetch('/ai/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ prompt })
                });

                const data = await response.json();
                hideLoading('query');

                if (data.success) {
                    showResult('query', data.response);
                } else {
                    showResult('query', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                hideLoading('query');
                showResult('query', 'Error: ' + error.message);
            }
        }

        // Explain Code
        async function submitCodeExplanation(event) {
            event.preventDefault();
            const code = document.getElementById('code-input').value;
            const language = document.getElementById('code-language').value;

            if (!code.trim()) return;

            showLoading('code');
            hideResult('code');

            try {
                const response = await fetch('/ai/explain-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ code, language })
                });

                const data = await response.json();
                hideLoading('code');

                if (data.success) {
                    showResult('code', data.response);
                } else {
                    showResult('code', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                hideLoading('code');
                showResult('code', 'Error: ' + error.message);
            }
        }

        // Summarize
        async function submitSummarize(event) {
            event.preventDefault();
            const text = document.getElementById('summarize-text').value;

            if (!text.trim()) return;

            showLoading('summarize');
            hideResult('summarize');

            try {
                const response = await fetch('/ai/summarize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ text })
                });

                const data = await response.json();
                hideLoading('summarize');

                if (data.success) {
                    showResult('summarize', data.response);
                } else {
                    showResult('summarize', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                hideLoading('summarize');
                showResult('summarize', 'Error: ' + error.message);
            }
        }

        // Ask Question
        async function submitQuestion(event) {
            event.preventDefault();
            const question = document.getElementById('ask-question').value;
            const context = document.getElementById('ask-context').value;

            if (!question.trim()) return;

            showLoading('ask');
            hideResult('ask');

            try {
                const response = await fetch('/ai/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ question, context })
                });

                const data = await response.json();
                hideLoading('ask');

                if (data.success) {
                    showResult('ask', data.response);
                } else {
                    showResult('ask', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                hideLoading('ask');
                showResult('ask', 'Error: ' + error.message);
            }
        }

        // Helper functions
        function showLoading(type) {
            document.getElementById(type + '-loading').classList.remove('hidden');
        }

        function hideLoading(type) {
            document.getElementById(type + '-loading').classList.add('hidden');
        }

        function showResult(type, text) {
            document.getElementById(type + '-result').classList.remove('hidden');
            document.getElementById(type + '-response').textContent = text;
        }

        function hideResult(type) {
            document.getElementById(type + '-result').classList.add('hidden');
        }
    </script>
</body>
</html>
