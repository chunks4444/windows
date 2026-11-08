<?php
// 방문자 분류 — User-Agent로 사람/봇을 나누고, 검색엔진 봇이면 어느 검색엔진인지, 사람이면 어디서 들어왔는지(Referer) 나눈다.
// 접속 통계(page_views)에 방문을 기록할 때 lib/logger.php가 부른다. 순수 함수라 따로 테스트하기 쉽다.
//
// 한계: UA를 스스로 밝히는 봇만 잡는다. 일반 Chrome처럼 위장한 크롤러는 'human'으로 남는다 — 그래서 자바스크립트가 실제로
// 실행됐을 때만 찍히는 비콘(api/log/client.php)으로 page_views.js_ok를 따로 표시해 "JS 실행 확인" 수치를 같이 본다.
// 검색엔진 UA(Googlebot 등)는 누구나 흉내 낼 수 있어서, 이 분류는 통계용이지 보안 판단용이 아니다.

const VISITOR_HUMAN   = 'human';
const VISITOR_SEARCH  = 'search_bot';   // 검색엔진 봇 (구글·네이버·다음·빙 …)
const VISITOR_AI      = 'ai_bot';       // AI 학습/답변용 봇 (GPTBot, ClaudeBot …)
const VISITOR_PREVIEW = 'preview_bot';  // 링크 미리보기 (카카오톡·페이스북·슬랙 …)
const VISITOR_OTHER   = 'other_bot';    // SEO·모니터링·스캐너·스크립트·UA 없음 등

// [정규식, 표시 이름] — 위에서부터 먼저 맞는 것을 쓴다.
const VISITOR_SEARCH_BOTS = [
    ['/Googlebot|Google-InspectionTool|GoogleOther|Storebot-Google|AdsBot-Google|Mediapartners-Google|APIs-Google|FeedFetcher-Google|Google-Site-Verification|Google-Read-Aloud|Google-PageRenderer|Google Favicon|GoogleProducer/i', '구글 (Googlebot)'],
    ['/Yeti\/|NaverBot|Naverbot|NaverSearch|Naver Blog Rssbot/i', '네이버 (Yeti)'],
    ['/Daumoa|DaumCrawler|DaumBot/i',                    '다음 (Daumoa)'],
    ['/bingbot|BingPreview|adidxbot|msnbot/i',           '빙 (Bing)'],
    ['/Slurp|YahooSeeker/i',                             '야후'],
    ['/Baiduspider/i',                                   '바이두'],
    ['/YandexBot|YandexImages|YandexMobileBot/i',        '얀덱스'],
    ['/DuckDuckBot/i',                                   'DuckDuckGo'],
    ['/Sogou/i',                                         '소고우'],
    ['/Applebot(?!-Extended)/i',                         '애플 (Applebot)'],
    ['/PetalBot/i',                                      '페탈 (Huawei)'],
    ['/SeznamBot|Qwantify|Mojeek|Ecosia|BraveBot/i',     '기타 검색엔진'],
];

const VISITOR_AI_BOTS = [
    ['/GPTBot|ChatGPT-User|OAI-SearchBot|OpenAI/i',                              'OpenAI (ChatGPT)'],
    ['/ClaudeBot|Claude-User|Claude-SearchBot|Claude-Web|anthropic-ai/i',        'Anthropic (Claude)'],
    ['/PerplexityBot|Perplexity-User/i',                                         'Perplexity'],
    ['/Bytespider|TikTokSpider/i',                                               'ByteDance'],
    ['/CCBot/i',                                                                 'Common Crawl'],
    ['/Amazonbot/i',                                                             'Amazon'],
    ['/Meta-ExternalAgent|Meta-ExternalFetcher|FacebookBot/i',                   'Meta AI'],
    ['/Applebot-Extended/i',                                                     'Apple AI'],
    ['/Google-Extended|Gemini-Deep-Research|Google-CloudVertexBot/i',            'Google AI'],
    ['/cohere-ai|cohere-training|MistralAI-User|DuckAssistBot|YouBot|Diffbot|ImagesiftBot|Omgili|AI2Bot|Timpibot|PanguBot|Kangaroo Bot/i', '기타 AI'],
];

const VISITOR_PREVIEW_BOTS = [
    ['/kakaotalk-scrap/i',                 '카카오톡'],
    ['/facebookexternalhit|Facebot/i',     '페이스북'],
    ['/Twitterbot/i',                      'X (트위터)'],
    ['/Slackbot|Slack-ImgProxy/i',         '슬랙'],
    ['/TelegramBot/i',                     '텔레그램'],
    ['/LinkedInBot/i',                     '링크드인'],
    ['/Discordbot/i',                      '디스코드'],
    ['/WhatsApp/i',                        '왓츠앱'],
    ['/Pinterestbot/i',                    '핀터레스트'],
    ['/SkypeUriPreview|Embedly|Iframely|LINE-Preview/i', '기타 미리보기'],   // 'Line/'은 LINE 앱 안의 실제 브라우저 UA라 넣지 않는다
];

const VISITOR_OTHER_BOTS = [
    ['/SemrushBot|AhrefsBot|MJ12bot|DotBot|BLEXBot|DataForSeoBot|serpstatbot|Barkrowler|SEOkicks|rogerbot|Screaming Frog|SiteAuditBot/i', 'SEO 도구'],
    ['/UptimeRobot|Pingdom|StatusCake|Site24x7|GTmetrix|Chrome-Lighthouse|PageSpeed|Datadog|NewRelic|Better Uptime|Freshping/i', '모니터링'],
    ['/zgrab|masscan|Nmap|sqlmap|Nikto|Nuclei|CensysInspect|Expanse|Shodan|Palo Alto|NetSystemsResearch|l9explore|l9tcpid/i', '스캐너'],
    ['/curl\/|Wget|python-requests|python-urllib|aiohttp|httpx|Go-http-client|libwww-perl|Java\/|Scrapy|node-fetch|axios|PostmanRuntime|okhttp-bot|HeadlessChrome|PhantomJS|Puppeteer|Playwright|Apache-HttpClient|GuzzleHttp|Faraday|Ruby/i', '스크립트·자동화'],
];

// 위 목록에 없지만 스스로 봇이라고 밝히는 UA
const VISITOR_GENERIC_BOT = '/bot\b|bot\/|crawl|spider|scrape|fetcher|monitor|checker/i';

// UA → [종류, 봇 이름|null]
function pm_classify_ua(string $ua): array {
    $ua = trim($ua);
    if ($ua === '') return [VISITOR_OTHER, '(UA 없음)'];

    foreach ([[VISITOR_SEARCH, VISITOR_SEARCH_BOTS], [VISITOR_AI, VISITOR_AI_BOTS], [VISITOR_PREVIEW, VISITOR_PREVIEW_BOTS], [VISITOR_OTHER, VISITOR_OTHER_BOTS]] as [$type, $list]) {
        foreach ($list as [$re, $name]) {
            if (preg_match($re, $ua)) return [$type, $name];
        }
    }
    if (preg_match(VISITOR_GENERIC_BOT, $ua)) return [VISITOR_OTHER, '기타 봇'];

    // 진짜 브라우저 UA에는 운영체제 표기가 반드시 있다. 없으면 브라우저가 아니라 스크립트·크롤러다.
    if (!preg_match('/Windows|Macintosh|Mac OS|iPhone|iPad|iPod|Android|Linux|X11|CrOS|FreeBSD/i', $ua)) {
        return [VISITOR_OTHER, '(브라우저 아님)'];
    }
    return [VISITOR_HUMAN, null];
}

// Referer 호스트 → 유입 경로. 'direct'는 주소 직접 입력·북마크·앱 등 Referer가 없는 경우.
function pm_ref_source(string $host): string {
    $h = strtolower(trim($host));
    if ($h === '' || $h === '-') return 'direct';
    if (preg_match('/(^|\.)pyeongmok\.com$/', $h)) return 'internal';
    if (preg_match('/(^|\.)google\.|googlequicksearchbox/', $h)) return 'google';
    if (preg_match('/(^|\.)naver\.(com|me)$|(^|\.)naver\.net$/', $h)) return 'naver';
    if (preg_match('/(^|\.)daum\.net$/', $h)) return 'daum';
    if (preg_match('/(^|\.)bing\.com$/', $h)) return 'bing';
    if (preg_match('/(^|\.)(yahoo\.|duckduckgo\.com|baidu\.com|sogou\.com|yandex\.|ecosia\.org|brave\.com|qwant\.com|zum\.com|nate\.com)/', $h)) return 'other_search';
    if (preg_match('/(^|\.)(facebook\.com|instagram\.com|threads\.(com|net)|t\.co|twitter\.com|x\.com|youtube\.com|youtu\.be|pinterest\.|linkedin\.com|lnkd\.in|reddit\.com|tiktok\.com|band\.us|kakao\.com|kakaocdn\.net|open\.kakao\.com|tistory\.com|brunch\.co\.kr|medium\.com)$/', $h)) return 'sns';
    return 'other';
}

// 표시용 이름 (통계 화면)
const VISITOR_TYPE_LABELS = [
    VISITOR_HUMAN   => '사람',
    VISITOR_SEARCH  => '검색엔진 봇',
    VISITOR_AI      => 'AI 봇',
    VISITOR_PREVIEW => '링크 미리보기',
    VISITOR_OTHER   => '기타 봇',
];
const VISITOR_SOURCE_LABELS = [
    'google' => '구글', 'naver' => '네이버', 'daum' => '다음', 'bing' => '빙', 'other_search' => '기타 검색',
    'sns' => 'SNS·커뮤니티', 'internal' => '사이트 내 이동', 'direct' => '직접 접속', 'other' => '기타 사이트',
];
