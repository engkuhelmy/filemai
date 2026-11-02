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

        // Admin or Creator can add episodes to dramas
        if ($user->isAdmin() || $user->isCreator()) {
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
        $videoType = $this->input('video_type', 'storage');

        $rules = [
            'video_type' => [
                'required',
                'in:storage,youtube',
            ],
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

        if ($videoType === 'youtube') {
            $rules['youtube_url'] = [
                'required',
                'string',
                new ValidYouTubeUrl(),
            ];
        } else {
            $rules['video_file'] = [
                'nullable',
                'file',
                'mimes:mp4,webm,mov,m4v',
                'max:204800', // Max 200MB
            ];
            $rules['video_url'] = [
                'nullable',
                'string',
                'max:500',
            ];
        }

        return $rules;
    }

    /**
     * Additional contextual validation.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('video_type', 'storage') === 'storage') {
                $hasFile = $this->hasFile('video_file') && $this->file('video_file')->isValid();
                $hasUrl = !empty(trim((string)$this->input('video_url', '')));
                if (!$hasFile && !$hasUrl) {
                    $validator->errors()->add('video_file', 'Sila muat naik fail video (MP4/WebM) atau sediakan pautan fail video langsung.');
                }
            }
        });
    }

    /**
     * Custom error messages in Bahasa Melayu.
     */
    public function messages(): array
    {
        return [
            'video_type.required' => 'Sila pilih jenis sumber video (Storan Terus atau YouTube).',
            'video_type.in' => 'Jenis sumber video tidak sah.',
            'title.required' => 'Tajuk episod wajib diisi.',
            'episode_number.required' => 'Nombor episod wajib diisi.',
            'episode_number.unique' => 'Nombor episod ini telah wujud dalam siri drama ini. Sila pilih nombor episod seterusnya.',
            'youtube_url.required' => 'Pautan video YouTube wajib diisi apabila memilih sumber YouTube.',
            'video_file.mimes' => 'Format fail video mestilah MP4, WebM, MOV, atau M4V.',
            'video_file.max' => 'Saiz fail video maksimum ialah 200MB.',
        ];
    }
}
