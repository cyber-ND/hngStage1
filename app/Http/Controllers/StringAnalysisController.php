<?php

namespace App\Http\Controllers;

use App\Models\StringAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StringAnalysisController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $value = $request->input('value');
        $hash = hash('sha256', $value);

        if (StringAnalysis::where('id', $hash)->exists()) {
            return response()->json(['error' => 'String already exists in the system'], 409);
        }

        $properties = $this->computeProperties($value, $hash);

        $analysis = StringAnalysis::create([
            'id' => $hash,
            'value' => $value,
            'properties' => $properties,
        ]);

        return response()->json([
            'id' => $hash,
            'value' => $value,
            'properties' => $properties,
            'created_at' => $analysis->created_at->toISOString(),
        ], 201);
    }

    public function show(Request $request, $string_value)
    {
        $hash = hash('sha256', $string_value);
        $analysis = StringAnalysis::find($hash);

        if (!$analysis) {
            return response()->json(['error' => 'String does not exist in the system'], 404);
        }

        return response()->json([
            'id' => $analysis->id,
            'value' => $analysis->value,
            'properties' => $analysis->properties,
            'created_at' => $analysis->created_at->toISOString(),
        ]);
    }

    public function index(Request $request)
    {
        // Normalize boolean string (e.g., "true" → true)
        $data = $request->all();
        if (isset($data['is_palindrome'])) {
            $data['is_palindrome'] = filter_var($data['is_palindrome'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $rules = [
            'is_palindrome' => 'sometimes|boolean',
            'min_length' => 'sometimes|integer|min:0',
            'max_length' => 'sometimes|integer|min:0',
            'word_count' => 'sometimes|integer|min:0',
            'contains_character' => 'sometimes|string|size:1',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $query = StringAnalysis::query();

        if (isset($data['is_palindrome'])) {
            $query->where('properties->is_palindrome', $data['is_palindrome']);
        }
        if (isset($data['min_length'])) {
            $query->where('properties->length', '>=', $data['min_length']);
        }
        if (isset($data['max_length'])) {
            $query->where('properties->length', '<=', $data['max_length']);
        }
        if (isset($data['word_count'])) {
            $query->where('properties->word_count', $data['word_count']);
        }
        if (isset($data['contains_character'])) {
            $char = $data['contains_character'];
            $query->where('properties->character_frequency_map->' . $char, '>', 0);
        }

        // Optional pagination
        $analyses = $query->orderByDesc('created_at')->paginate(20);

        $dataOut = $analyses->getCollection()->map(function ($analysis) {
            return [
                'id' => $analysis->id,
                'value' => $analysis->value,
                'properties' => $analysis->properties,
                'created_at' => $analysis->created_at->toISOString(),
            ];
        });

        return response()->json([
            'data' => $dataOut,
            'count' => $dataOut->count(),
            'filters_applied' => $validator->validated(),
            'pagination' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'total' => $analyses->total(),
            ],
        ]);
    }

    public function naturalFilter(Request $request)
    {
        $original = urldecode($request->query('query', ''));
        if (empty($original)) {
            return response()->json(['error' => 'Missing query parameter'], 400);
        }

        $parsed = $this->parseNaturalQuery($original);
        if (empty($parsed)) {
            return response()->json(['error' => 'Unable to parse natural language query'], 400);
        }

        if (isset($parsed['min_length'], $parsed['max_length']) && $parsed['min_length'] > $parsed['max_length']) {
            return response()->json(['error' => 'Query parsed but resulted in conflicting filters'], 422);
        }

        $query = StringAnalysis::query();

        if (isset($parsed['is_palindrome'])) {
            $query->where('properties->is_palindrome', $parsed['is_palindrome']);
        }
        if (isset($parsed['min_length'])) {
            $query->where('properties->length', '>=', $parsed['min_length']);
        }
        if (isset($parsed['max_length'])) {
            $query->where('properties->length', '<=', $parsed['max_length']);
        }
        if (isset($parsed['word_count'])) {
            $query->where('properties->word_count', $parsed['word_count']);
        }
        if (isset($parsed['contains_character'])) {
            $char = $parsed['contains_character'];
            $query->where('properties->character_frequency_map->' . $char, '>', 0);
        }

        $analyses = $query->get();

        if ($analyses->isEmpty()) {
            return response()->json(['error' => 'No matching strings found in the system'], 404);
        }

        $data = $analyses->map(function ($analysis) {
            return [
                'id' => $analysis->id,
                'value' => $analysis->value,
                'properties' => $analysis->properties,
                'created_at' => $analysis->created_at->toISOString(),
            ];
        });

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
            'interpreted_query' => [
                'original' => $original,
                'parsed_filters' => $parsed,
            ],
        ]);
    }

    public function destroy(Request $request, $string_value)
    {
        $hash = hash('sha256', $string_value);
        $analysis = StringAnalysis::find($hash);

        if (!$analysis) {
            return response()->json(['error' => 'String does not exist in the system'], 404);
        }

        $analysis->delete();
        return response('', 204);
    }

    private function computeProperties($value, $hash)
    {
        $lowerValue = strtolower($value);
        $isPalindrome = $lowerValue === strrev($lowerValue);
        $charArray = str_split($value);
        $uniqueChars = count(array_unique($charArray));

        return [
            'length' => strlen($value),
            'is_palindrome' => $isPalindrome,
            'unique_characters' => $uniqueChars,
            'word_count' => str_word_count($value),
            'sha256_hash' => $hash,
            'character_frequency_map' => array_count_values($charArray),
        ];
    }

    private function parseNaturalQuery($query)
    {
        $parsed = [];
        $lower = strtolower($query);

        if (strpos($lower, 'palindrom') !== false) {
            $parsed['is_palindrome'] = true;
        }

        if (strpos($lower, 'single word') !== false) {
            $parsed['word_count'] = 1;
        }

        if (preg_match('/longer than (\d+) characters?/', $lower, $matches)) {
            $parsed['min_length'] = (int) $matches[1] + 1;
        }

        if (preg_match('/containing the letter ([a-z])/', $lower, $matches)) {
            $parsed['contains_character'] = strtolower($matches[1]);
        }

        if (strpos($lower, 'first vowel') !== false) {
            $parsed['contains_character'] = 'a';
        }

        return $parsed;
    }
}

