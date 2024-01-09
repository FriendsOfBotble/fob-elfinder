<?php

namespace FriendsOfBotble\ElFinder\Forms\Settings;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use FriendsOfBotble\ElFinder\Http\Requests\ElFinderSettingRequest;

class ElFinderSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/elfinder::elfinder.settings.title'))
            ->setSectionDescription(trans('plugins/elfinder::elfinder.settings.description'))
            ->setValidatorClass(ElFinderSettingRequest::class)
            ->when(BaseHelper::getRichEditor() !== 'ckeditor', function (ElFinderSettingForm $form) {
                $form->add(
                    'editor_not_support',
                    HtmlField::class,
                    HtmlFieldOption::make()
                        ->bladeContent(
                            sprintf(
                                '<x-core::alert type="warning">%s</x-core::alert>',
                                trans('plugins/elfinder::elfinder.settings.editor_not_support', [
                                    'editor' => BaseHelper::getRichEditor(),
                                ])
                            )
                        )
                        ->toArray()
                );
            })
            ->add(
                'elfinder_editor_enabled',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/elfinder::elfinder.settings.form.enable_editor'))
                    ->value(setting('elfinder_editor_enabled', true))
                    ->toArray()
            )
            ->add(
                'elfinder_replace_default_media',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/elfinder::elfinder.settings.form.replace_default_media'))
                    ->value(setting('elfinder_replace_default_media', false))
                    ->helperText(trans('plugins/elfinder::elfinder.settings.form.replace_default_media_helper'))
                    ->toArray()
            )
            ->add(
                'elfinder_base_path',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/elfinder::elfinder.settings.form.base_path'))
                    ->helperText(trans('plugins/elfinder::elfinder.settings.form.base_path_helper'))
                    ->value(setting('elfinder_base_path', 'storage'))
                    ->toArray()
            );
    }
}
