<?php

namespace App\Enums;

enum ClientSource: string
{
    case GUARDIAN = 'guardian';
    case NEW_YORK_TIME = 'nyt';
    case NEWS_API = 'news_api';
}
