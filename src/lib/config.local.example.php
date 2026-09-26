<?php
// 이 파일을 config.local.php로 복사해서 실제 값을 채울 것. config.local.php는 .gitignore 대상이라 git에 안 올라감.
// 운영서버·로컬 개발 PC(맥/윈도우) 각각에 하나씩 있어야 하고, 없으면 DB 접속과 로그인이 전부 안 된다.
//
// PMOK_DB_PASS    : MySQL webpyeongmok 계정 비밀번호 (로컬은 SSH 터널로 같은 운영 DB에 붙으므로 같은 값)
// PMOK_JWT_SECRET : 로그인 쿠키(pmok_auth) 서명 키. 바꾸면 모든 회원이 로그아웃됨. 생성: openssl rand -hex 32
define('PMOK_DB_PASS',    '');
define('PMOK_JWT_SECRET', '');
