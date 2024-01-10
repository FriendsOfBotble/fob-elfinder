<?php

namespace FriendsOfBotble\ElFinder\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;
use Closure;
use Illuminate\Support\Str;

class ElFinderSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'elfinder_editor_enabled' => new OnOffRule(),
            'elfinder_replace_default_media' => new OnOffRule(),
            'elfinder_base_path' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, Closure $fail) {
                if (Str::startsWith($value, ['.', DIRECTORY_SEPARATOR]) || Str::endsWith($value, DIRECTORY_SEPARATOR)) {
                    $fail(trans('plugins/elfinder::elfinder.settings.form.base_path_invalid'));
                }

                if (Str::startsWith($value, ['themes', 'vendor'])) {
                    $fail(trans('plugins/elfinder::elfinder.settings.form.base_path_does_not_starts_with_vendor_or_themes'));
                }
            }],
        ];
    }
}
