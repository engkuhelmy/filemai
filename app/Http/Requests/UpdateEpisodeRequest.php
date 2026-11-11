<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidYouTubeUrl;
use App\Models\Drama;
use App\Models\Episode;

class UpdateEpisodeRequest extends FormRequest
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

        if ($user->isAdmin()) {
            return true;
        }

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
        $episode = $this->route('episode');
        $dramaId = $drama instanceof Drama ? $drama->id : $this->input('drama_id');
        $episodeId = $episode instanceof Episode ? $episode->id : $episode;
        $videoType = $this->input('video_type', 'current');

        $rules = [
            'video_type' => [
                'required',
                'in:current,storage,youtube',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('episodes', 'episode_number')
                    ->where(function ($query) use ($dramaId) {
                        return $query->where('drama_id', $dramaId);
                    })
                    ->ignore($episodeId),
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:3600',
            ],
            'status' => [
                'required',
                'in:draft,scheduled,published',
            ],
            'scheduled_at' => [
                'nullable',
                'date',
            ],
            'is_free_preview' => [
                'nullable',
                'boolean',
            ],
            'thumbnail_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'remove_custom_thumbnail' => [
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
        } elseif ($videoType === 'storage') {
            $rules['video_file'] = [
                'nullable',
                'file',
                'mimes:mp4,webm,mov,m4v',
                'max:204800',
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
            if ($this->input('video_type') === 'storage') {
                $hasFile = $this->hasFile('video_file') && $this->file('video_file')->isValid();
                $hasUrl = !empty(trim((string)$this->input('video_url', '')));
                $episode = $this->route('episode');
                $hasExistingUrl = $episode instanceof Episode && !empty($episode->video_url);

                if (!$hasFile && !$hasUrl && !$hasExistingUrl) {
                    $validator->errors()->add('video_file', 'Sila muat naik fail video (MP4/WebM) atau sediakan pautan fail video langsung.');
                }
            }

            if ($this->input('status') === 'scheduled' && empty($this->input('scheduled_at'))) {
                $validator->errors()->add('scheduled_at', 'Sila tetapkan tarikh dan masa untuk jadual tayangan.');
            }
        });
    }

    /**
     * Custom error messages in Bahasa Melayu.
     */
    public function messages(): array
    {
        return [
            'video_type.required' => 'Sila pilih jenis sumber video.',
            'title.required' => 'Tajuk episod wajib diisi.',
            'episode_number.required' => 'Nombor episod wajib diisi.',
            'episode_number.unique' => 'Nombor episod ini telah digunakan oleh episod lain dalam siri ini.',
            'youtube_url.required' => 'Pautan video YouTube wajib diisi.',
            'video_file.mimes' => 'Format fail video mestilah MP4, WebM, MOV, atau M4V.',
            'video_file.max' => 'Saiz fail video maksimum ialah 200MB.',
            'thumbnail_file.mimes' => 'Format gambar kenit mestilah JPG, PNG atau WEBP.',
            'thumbnail_file.max' => 'Saiz gambar kenit maksimum ialah 5MB.',
            'scheduled_at.required_if' => 'Sila tetapkan tarikh dan masa untuk jadual tayangan.',
        ];
    }
}

