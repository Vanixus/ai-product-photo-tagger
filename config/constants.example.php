<?php

declare(strict_types=1);

const APP_NAME = 'Product Photo Tagger';
const APP_BASE_URL = ''; // Leave empty if hosted at domain root

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'ai_product_tagger';
const DB_USER = 'your_db_user';
const DB_PASS = 'your_db_password';
const DB_CHARSET = 'utf8mb4';

define('DEMO_PASSWORD', 'your_demo_password');
define('ADMIN_PASSWORD', 'your_admin_password');

const ANTHROPIC_API_KEY = 'your_anthropic_api_key';
const ANTHROPIC_MODEL = 'claude-haiku-4-5-20251001';
const ANTHROPIC_VERSION = '2023-06-01';
const ANTHROPIC_TIMEOUT_SECONDS = 30;

const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;
const UPLOADS_DIR = __DIR__ . '/../uploads';
const UPLOADS_WEB_PATH = 'uploads';

const SYSTEM_PROMPT = <<<'PROMPT'
You are a product cataloging assistant. Your job is to analyze product images and return structured metadata for internal use.

Always respond ONLY with a valid JSON object. No explanation, no markdown, no extra text.

The JSON must follow this exact structure:
{
  "tags": ["tag1", "tag2", "tag3", "..."],
  "description": "Short internal description of the product, 2-3 sentences."
}

Tag guidelines:
- Include: product category, material or fabric, color(s), style, gender target if clear, use case
- Use lowercase, singular form (e.g. "jacket" not "Jackets")
- Return between 5 and 12 tags per image
- Avoid vague tags like "nice" or "product"
PROMPT;