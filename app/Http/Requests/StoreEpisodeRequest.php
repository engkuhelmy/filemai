<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidYouTubeUrl;
use App\Models\Drama;

class StoreEpisodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Admin can add episodes to any drama
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can only add to their own drama
        $drama = $this->route('drama');
        if ($drama instanceof Drama) {
            return $drama->creator_id === $user->id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $drama = $this->route('drama');
        $dramaId = $drama instanceof Drama ? $drama->id : $this->input('drama_id');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('episodes', 'episode_number')->where(function ($query) use ($dramaId) {
                    return $query->where('drama_id', $dramaId);
                }),
            ],
            'youtube_url' => [
                'required',
                'string',
                new ValidYouTubeUrl(),
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:3600',
            ],
            'is_free_preview' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Custom error messages in Bahasa Melayu.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tajuk episod wajib diisi.',
            'episode_number.required' => 'Nombor episod wajib diisi.',
            'episode_number.unique' => 'Nombor episod ini telah wujud dalam siri drama ini. Sila pilih nombor episod seterusnya.',
            'youtube_url.required' => 'Pautan video YouTube wajib diisi.',
        ];
    }
}
