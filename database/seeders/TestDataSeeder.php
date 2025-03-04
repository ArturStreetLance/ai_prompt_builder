<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prompt;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Создаем тестового пользователя
        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]);
        if (!$user->profile) {
            $user->profile()->create([
                'bio' => 'Пока ничего о себе не рассказал',
                'avatar' => null,
            ]);
        }

        // Обновляем профиль пользователя
        $user?->profile?->update([
            'bio' => 'AI энтузиаст и разработчик промптов',
            'website' => 'https://example.com',
            'notifications_enabled' => true,
            'public_profile' => true,
            'theme' => 'dark',
            'prompts_created' => 5,
            'total_usage' => 1250,
            'rating' => 4.8,
        ]);

        // Создаем тестовые промпты
        $prompts = [
            [
                'name' => 'Генератор статей',
                'content' => 'Напиши информативную статью на тему [ТЕМА] объемом [КОЛИЧЕСТВО] слов. Используй подзаголовки, списки и примеры.',
                'category' => 'Копирайтинг',
                'rating' => 4.8,
                'usage_count' => 1250,
            ],
            [
                'name' => 'Анализ кода',
                'content' => 'Проанализируй следующий код и предложи улучшения с точки зрения производительности, безопасности и читаемости: [КОД]',
                'category' => 'Разработка',
                'rating' => 4.9,
                'usage_count' => 890,
            ],
            [
                'name' => 'Email маркетинг',
                'content' => 'Создай email-рассылку для [ПРОДУКТ/СЕРВИС] с целью [ЦЕЛЬ]. Используй убедительный призыв к действию.',
                'category' => 'Маркетинг',
                'rating' => 4.7,
                'usage_count' => 750,
            ],
            [
                'name' => 'SEO оптимизация',
                'content' => 'Оптимизируй следующий текст для SEO, сохраняя естественность и читаемость. Ключевые слова: [КЛЮЧЕВЫЕ_СЛОВА]',
                'category' => 'SEO',
                'rating' => 4.9,
                'usage_count' => 2300,
            ],
            [
                'name' => 'Креативное письмо',
                'content' => 'Напиши креативную историю в жанре [ЖАНР] с неожиданным поворотом сюжета. Используй яркие описания и диалоги.',
                'category' => 'Творчество',
                'rating' => 4.8,
                'usage_count' => 1800,
            ],
        ];

        foreach ($prompts as $promptData) {
            $user->prompts()->create($promptData);
        }
    }
}
