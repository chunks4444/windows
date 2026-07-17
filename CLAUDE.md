# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 프로젝트 개요

**평목(平木)** — 한국 전통 창호(살창) 실시간 설계 스튜디오. 브라우저에서 도면을 설계하고 공방에 제작을 주문하는 서비스.

스택: PHP (프레임워크 없음), 바닐라 JS, MySQL (PDO), 순수 CSS. 빌드 툴 없음. 파일을 수정하면 즉시 반영.

## 개발 명령어

```bash
# PHP 문법 검사
php -l src/lib/svg_sanitize.php

# JS 문법 검사
node --check src/js/engine-common.js
node --check src/js/classic.js

# DB는 외부에 직접 노출되지 않음 (MySQL bind-address 127.0.0.1) — 로컬 개발 전에 SSH 터널이 떠 있어야 함
# Windows: ops/db_tunnel/start_tunnel.ps1 (로그인 시 자동 실행되도록 register_task.ps1로 등록 가능)
# macOS:   ops/db_tunnel/start_tunnel.sh
# 터널이 127.0.0.1:13306을 열어주면 src/lib/db.php가 자동으로 그 쪽으로 접속함

# 로컬 개발 서버 (포트 8899)
php -S localhost:8899 -t .

# PHP 에러 로그 확인
tail -f logs/php-error.log

# DB 스키마 확인
cat src/lib/schema.sql
```

## 아키텍처

### 디렉토리 구조

```
/
├── index.php                  # 메인 홈 페이지
├── src/
│   ├── engine/{type}/         # 설계 엔진 (classic/square/cross/diamond/triangle/hexagon)
│   │   ├── {type}.php         # 엔진 HTML 페이지 (사이드바 컨트롤 + canvas)
│   │   └── api/               # 엔진별 AI 렌더링 API (render.php, render_worker.php, render_poll.php)
│   ├── js/
│   │   ├── engine-common.js   # 모든 엔진이 공유하는 drawing/save/SVG 로직 (~1200줄)
│   │   └── {type}.js          # 엔진별 기하학 계산 및 canvas 그리기 로직
│   ├── api/
│   │   ├── auth/              # 로그인·회원가입·OAuth·비밀번호 재설정
│   │   ├── drawings/          # 도면 저장·불러오기·삭제
│   │   ├── admin/             # 어드민 전용 API (role='s' 필요)
│   │   ├── uploads/svg_insert.php  # 사용자 SVG 업로드
│   │   └── wallpapers/        # 배경 이미지 업로드·목록·삭제
│   ├── lib/
│   │   ├── db.php             # PDO 싱글턴 db()
│   │   ├── jwt.php            # JWT encode/decode/jwt_from_request()
│   │   ├── drawing.php        # Drawing 클래스 (save/load/delete)
│   │   ├── engine_settings.php # 엔진별 기본값 + DB 오버라이드
│   │   ├── ai_render.php      # OpenAI gpt-image-1 공용 함수
│   │   ├── svg_sanitize.php   # SVG 배경 rect 제거 (svg_strip_background())
│   │   └── schema.sql         # DB 스키마 (마이그레이션 없음, ALTER 주석 형태)
│   ├── components/            # PHP include 컴포넌트 (nav, auth_modal, footer 등)
│   └── css/engine-common.css  # 엔진 공통 CSS (CSS 변수 기반 디자인 시스템)
└── uploads/
    ├── svg_insert/{userId}/   # 사용자 업로드 SVG 문양
    ├── svg_motifs/            # 어드민 등록 SVG 라이브러리
    └── wallpapers/            # 엔진 배경 이미지
```

### 설계 엔진 동작 방식

엔진 페이지(`{type}.php`)는 PHP가 사이드바 컨트롤 HTML과 초기 설정값(`engine_settings` DB 테이블 → `get_engine_settings()` fallback)을 렌더링한다. **도면 계산과 canvas 그리기는 100% 클라이언트 JS**에서 처리한다.

JS 역할 분리:
- `engine-common.js`: 저장/불러오기, 버전 관리, SVG 문양 삽입, 배경 이미지, AI 렌더링 폴링, 내보내기(PNG/PDF), 공통 모달
- `{type}.js`: 해당 패턴의 기하학 계산(`draw()` 함수), 선 편집, 색상 페인트 등 엔진 고유 기능

### 인증

JWT를 httpOnly 쿠키(`pmok_auth`)로 발급. `jwt_from_request()`가 쿠키 → `Authorization: Bearer` 순서로 검증. JWT secret은 `.htaccess`의 `SetEnv PMOK_JWT_SECRET`으로 주입.

역할: `s`=슈퍼, `m`=관리자, `a`=작가, `u`=회원. 어드민 API는 `$payload['role'] === 's'` 확인.

### 데이터베이스

`db()` 함수가 PDO 싱글턴을 반환. 환경 감지:
- `HTTP_HOST === 'studio.pyeongmok.com'` → Unix 소켓 (`/var/run/mysqld/mysqld.sock`)
- 그 외 → TCP (`211.35.72.68:6836`)

스키마 변경은 마이그레이션 프레임워크 없이 수동 `ALTER TABLE`. 신규 테이블은 `schema.sql`에 `CREATE TABLE IF NOT EXISTS`로 추가.

### 도면 저장 구조

`Drawing::save()`는 `(user_id, type, title)` 복합 유니크 키로 upsert. 저장 시 `drawing_versions` 전체를 교체(DELETE → INSERT). 각 버전의 `params`는 엔진 사이드바 컨트롤 값 전체를 JSON으로 직렬화한 스냅샷.

### AI 렌더링

비동기 처리: `render.php` → `proc_open()`으로 `render_worker.php` PHP 프로세스 백그라운드 실행 → 결과를 `/tmp/pmok_render/{jobId}.result.json`에 저장 → 브라우저가 `render_poll.php`를 폴링해 결과 수신.

OpenAI API 키 우선순위: `OPENAI_API_KEY` env > `site_config` DB 테이블 > `src/engine/config.local.php`.

### CSS

엔진 페이지의 CSS 링크는 `md5_file()`로 캐시 버스팅:
```php
href="/src/css/engine-common.css?v=<?= md5_file(__DIR__ . '/../../css/engine-common.css') ?>"
```

디자인 시스템은 `engine-common.css`의 CSS 변수(`--teal`, `--text-1`, `--border` 등)로 통일. 공용 페이지(홈 등)는 Bootstrap 5 CDN 추가 사용.

### SVG 문양 파이프라인

업로드 시 `svg_strip_background()`로 배경 `<rect>`(뷰포트 95% 이상 크기 + 단색 fill)를 제거해 투명 배경으로 저장. canvas에서 `ctx.drawImage(img, ...)`로 렌더링.
