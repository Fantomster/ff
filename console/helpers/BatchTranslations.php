<?php

namespace console\helpers;

use common\models\SourceMessage;
use common\models\Message;

/**
 * Description of BatchTranslations
 *
 * @author elbabuino
 */
class BatchTranslations
{

    /**
     * @param array $data ['ru' => ['category' => ['common.generic.variable' => 'translation', ...]], 'en' => ...]
     */
    public static function insert($data)
    {
        foreach ($data as $language => $categories) {
            self::insertLanguage($language, $categories);
        }
    }

    /**
     * @param string $language
     * @param array $categories ['category' => ['common.generic.variable' => 'translation', ...]]
     */
    public static function insertLanguage($language, $categories)
    {
        foreach ($categories as $category => $translations) {
            self::insertCategory($language, $category, $translations);
        }
    }

    /**
     * @param string $language
     * @param string $category
     * @param array $translations ['common.generic.variable' => 'translation', ...]
     */
    public static function insertCategory($language, $category, $translations)
    {
        foreach ($translations as $var => $translation) {
            $source_message = SourceMessage::findOne(['message' => $var, 'category' => $category]);

            if (empty($source_message)) {
                $source_message           = new SourceMessage();
                $source_message->message  = $var;
                $source_message->category = $category;
                $source_message->save();
            }

            $message = Message::findOne(['id' => $source_message->id, 'language' => $language]);
            if (empty($message)) {
                $message = new Message();
            }

            $message->id          = $source_message->id;
            $message->language    = $language;
            $message->translation = $translation;
            $message->save();
        }
    }

    /**
     * @param array $data ['ru' => ['category' => ['common.generic.variable' => 'translation', ...]], 'en' => ...]
     */
    public static function delete($data)
    {
        foreach ($data as $language => $categories) {
            self::deleteLanguage($language, $categories);
        }
    }

    /**
     * @param string $language
     * @param array $categories ['category' => ['common.generic.variable' => 'translation', ...]]
     */
    public static function deleteLanguage($language, $categories)
    {
        foreach ($categories as $category => $translations) {
            self::deleteCategory($language, $category, $translations);
        }
    }

    /**
     * @param string $language
     * @param string $category
     * @param array $translations ['common.generic.variable' => 'translation', ...]
     */
    public static function deleteCategory($language, $category, $translations)
    {
        foreach ($translations as $var => $translation) {
            $source_message = SourceMessage::findOne(['message' => $var, 'category' => $category]);
            if (!empty($source_message)) {
                $message = Message::findOne(['id' => $source_message->id, 'language' => $language]);
                if (!empty($message)) {
                    $message->delete();
                }
                $source_message->delete();
            }
        }
    }

}
