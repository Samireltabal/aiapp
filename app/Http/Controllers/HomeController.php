<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected OllamaService $ollama;

    public function __construct(OllamaService $ollama)
    {
        $this->ollama = $ollama;
    }

    /**
     * Display the home page
     */
    public function index()
    {
        $isOllamaAvailable = $this->ollama->isAvailable();
        $models = $isOllamaAvailable ? $this->ollama->listModels() : ['success' => false];

        return view('home', [
            'isOllamaAvailable' => $isOllamaAvailable,
            'models' => $models['models'] ?? [],
        ]);
    }

    /**
     * Handle AI query from the form
     */
    public function query(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:5000',
            'model' => 'nullable|string',
        ]);

        $model = $request->input('model', config('services.ollama.default_model'));
        $prompt = $request->input('prompt');

        $result = $this->ollama->generate($prompt, $model);

        return response()->json($result);
    }

    /**
     * Example: Explain code
     */
    public function explainCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'language' => 'nullable|string',
        ]);

        $result = $this->ollama->explainCode(
            $request->input('code'),
            $request->input('language', 'php')
        );

        return response()->json($result);
    }

    /**
     * Example: Summarize text
     */
    public function summarize(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
        ]);

        $result = $this->ollama->summarize($request->input('text'));

        return response()->json($result);
    }

    /**
     * Example: Ask a question
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'context' => 'nullable|string|max:5000',
        ]);

        $result = $this->ollama->askQuestion(
            $request->input('question'),
            $request->input('context', '')
        );

        return response()->json($result);
    }
}
