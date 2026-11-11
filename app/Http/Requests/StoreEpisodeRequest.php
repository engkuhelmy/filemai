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

        $drama = $this->route('drama');
        if ($drama instanceof Drama) {
            return (int)$drama->creator_id === (int)$user->id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $drama = $this->route('drama');
        $dramaId = $drama instanceof Drama ? $drama->id : $drama;
        $videoType = $this->input('video_type', 'storage');

        $rules = [
            'video_type' => [
                'required',
                'in:storage,youtube,coming_soon',
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
                Rule::unique('episodes', 'episode_number')->where(function ($query) use ($dramaId) {
                    return $query->where('drama_id', $dramaId);
                })->ignore($this->route('episode')?->id),
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:3600',
            ],
            'status' => [
                'nullable',
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
            'thumbnail_url' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];

        if ($videoType === 'youtube') {
            $rules['youtube_url'] = [
                'nullable',
                'string',
                new ValidYouTubeUrl(),
            ];
        }

        if ($videoType === 'storage') {
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
     * Custom validation rules hook.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // If user explicitly chose storage with empty video and neither file nor URL is provided
            if ($this->input('video_type') === 'storage' && $this->input('media_required')) {
                $hasFile = $this->hasFile('video_file') && $this->file('video_file')->isValid();
                $hasUrl = !empty(trim((string)$this->input('video_url', '')));
                if (!$hasFile && !$hasUrl) {
                    $validator->errors()->add(
                        'video_file',
                        'Sila muat naik fail video (MP4) atau masukkan pautan video URL terus.'
                    );
                }
            }
        });
    }

    /**
     * Custom validation error messages in Bahasa Melayu.
     */
    public function messages(): array
    {
        return [
            'video_type.required' => 'Sila pilih jenis sumber video.',
            'video_type.in' => 'Pilihan jenis sumber video tidak sah.',
            'title.required' => 'Tajuk episod wajib diisi.',
            'episode_number.required' => 'Nombor episod wajib diisi.',
            'episode_number.unique' => 'Nombor episod ini sudah wujud dalam siri drama ini.',
            'youtube_url.required' => 'Pautan video YouTube wajib diisi apabila memilih sumber YouTube.',
            'video_file.mimes' => 'Format fail video mestilah MP4, WebM, MOV, atau M4V.',
            'video_file.max' => 'Saiz fail video maksimum ialah 200MB.',
            'thumbnail_file.mimes' => 'Format gambar kenit mestilah JPG, PNG atau WEBP.',
            'thumbnail_file.max' => 'Saiz gambar kenit maksimum ialah 5MB.',
        ];
    }
}
