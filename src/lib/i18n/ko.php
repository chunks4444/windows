<?php
// 한국어 UI 문구 (기본 언어). en.php에 없는 키는 자동으로 여기로 폴백된다.
return [
    // nav.php
    'nav_menu_open'            => '메뉴 열기',
    'nav_drawer_close'         => '닫기',
    'nav_engine_prompt_ph'     => '예: 완자살 미서기문 3짝, 가로 1800 세로 1200으로 바꿔줘',
    'nav_studio'               => '스튜디오',
    'nav_drawing_manage'       => '도면 관리',
    'nav_collection'           => '컬렉션',
    'nav_portfolio'            => '포트폴리오',
    'nav_guide'                => '가이드',
    'nav_guide_home'           => '가이드 홈',
    'nav_guide_intro'          => '스튜디오 소개',
    'nav_guide_studio_usage'   => '스튜디오 사용법',
    'nav_guide_render'         => 'AI 렌더링',
    'nav_guide_account'        => '계정 설정',
    'nav_guide_order'          => '주문',
    'nav_guide_delivery'       => '배송',
    'nav_guide_faq'            => 'FAQ',
    'nav_blog'                 => '블로그',
    'nav_company'              => '평목 소개',
    'nav_company_intro'        => '소개',
    'nav_company_studio'       => '스튜디오',
    'nav_company_contact'      => '연락처',
    'nav_login'                => '로그인',
    'nav_last_login'           => '마지막 접속',
    'nav_profile'              => '프로필',
    'nav_company_info'         => '회사 정보',
    'nav_orders'                => '주문내역',
    'nav_my_boards'            => '내 보드',
    'nav_logout'               => '로그아웃',
    'nav_admin'                => '어드민',
    'nav_lang_switch'          => 'English',

    // index.php — 히어로
    'home_h1'                  => '평목 - 나만의 한옥 살창·창호를 실시간으로 디자인하는 스튜디오',
    'home_hero_top'            => '같은 공간은 없으니까요. 치수와 빛에 맞춰 그립니다.',
    'home_hero_sub'            => '한옥 창호, 기법은 전통 — 모양은 그린 그대로 평목이 만듭니다',
    'home_ai_placeholder'      => '원하는 창호를 말해보세요  예: 정자살 여닫이 2짝 900×2000',
    'home_ai_send'             => '설계 시작',
    'home_engine_alt'          => '%s 패턴 미리보기',

    // 엔진 카드 기본 설명 (DB 값 없을 때 사용)
    'home_engine_desc_classic'  => '가는 살대를 성기게 세로·가로로만 짜 넣은 가장 단순한 전통 문살 패턴.<br>여백이 넓어 담백하고 개방감 있는 인상을 줍니다.',
    'home_engine_desc_square'   => '가로살과 세로살이 촘촘하게 井(우물 정)자를 이루며 교차하는 정방형 문살 패턴.<br>단아하고 절제된 아름다움을 표현합니다.',
    'home_engine_desc_cross'    => '45° 대각선으로 교차하는 마름모 문살 패턴.<br>역동적인 사선의 흐름이 공간에 긴장감을 더합니다.',
    'home_engine_desc_triangle' => "수직살과 좌우 빗살, 세 방향의 살대가 한 점에서 만나도록 짠 세모솟을살을 재현한 엔진입니다. '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 교차점마다 살이 도드라져 짜임에 입체감이 살아 있습니다. 살들이 교차하며 정삼각형이 화면 가득 반복되어, 육모의 둥글고 넉넉한 인상과 달리 팽팽하고 긴장감 있는 느낌을 줍니다. 모든 셀이 정삼각형이 되도록 세로 칸수가 자동으로 계산되며, 세로 칸수를 직접 지정할 수는 없습니다.",
    'home_engine_desc_diamond'  => '4방향 살이 대각선을 포함해 방사형으로 교차하는 패턴.<br>화려하고 입체적인 구조감을 연출합니다.',
    'home_engine_desc_hexagon'  => "세모솟을살과 같은 세 방향 살대를 쓰되, 교차점을 한 점에 모으지 않고 어긋나게 짜 육각형이 열리도록 한 육모솟을살을 재현한 엔진입니다. 어금육모라고도 부릅니다. '솟을'은 살이 교차점에서 겹치며 위로 솟아오르는 데서 온 이름으로, 짜임에 입체감이 살아 있습니다. 살이 만드는 벌집 모양의 여섯 각은 사각보다 원에 가까워, 같은 짜임인데도 세모의 팽팽함 대신 둥글고 넉넉한 인상을 줍니다.",

    // 컬렉션 스트립
    'home_collection_title'    => '마음에 드는 패턴을 골라 편집해보세요.',
    'home_collection_more'     => '컬렉션 전체 보기',
    'home_collection_prev'     => '이전 패턴 보기',
    'home_collection_next'     => '다음 패턴 보기',

    // 빛과 살
    'home_light_label'         => '빛과 살',
    'home_light_title'         => '한옥 창호는 빛을 막지 않고<br>나누어 들입니다',
    'home_light_p1a'           => '유리창은 빛을 통째로 들이고, 벽은 통째로 막습니다. 살은 그 사이에 있습니다. 막으면서 들이고, 들이면서 거릅니다.',
    'home_light_p1b'           => '살 간격이 촘촘하면 빛이 잘게 부서져 방 안이 고르게 밝아집니다. 성기면 덩어리로 들어와 바닥에 또렷한 그림자를 남깁니다. <b>같은 문양이라도 창이 앉는 방향과 시간에 따라 다르게 보입니다.</b>',
    'home_light_p2a'           => '그래서 옛 목수는 방마다 살을 달리 짰습니다. 안방과 대청이 같을 수 없고, 남향과 북향이 같을 수 없습니다.',
    'home_light_p2b'           => '스튜디오에서 살 간격을 옮기는 일은 무늬를 고르는 일처럼 보이지만, 실은 그 방에 들어올 빛을 정하는 일입니다.',

    // 살의 쓰임
    'home_usage2_label'        => '살의 쓰임',
    'home_usage2_title'        => '한식 창호와 목창호,<br>같은 살짜임으로 만듭니다',
    'home_usage2_sub'          => '한옥에 들어가는 창호든 현대 공간에 들어가는 목창호든, 짜는 문법은 하나입니다. 스케일만 다릅니다.',
    'home_line1_title'         => '한식 창호',
    'home_line1_body'          => '세살·정자살·완자살·교살·솟을살. 여닫이와 미서기, 들어열개까지. 살이 제 크기로 서는 자리입니다.',
    'home_line2_title'         => '목창호',
    'home_line2_body'          => '한옥이 아닌 공간에 들어가는 창과 문. 살은 그대로 두고 틀만 그 공간의 치수를 따릅니다.',
    'home_line3_title'         => '파티션',
    'home_line3_body'          => '벽을 세우지 않고 자리를 나눌 때. 살의 밀도가 시선이 어디까지 갈지를 정합니다.',
    'home_line4_title'         => '가구·기물',
    'home_line4_body'          => '같은 살을 손에 잡히는 크기로. 장의 문짝과 조명에서는 살이 훨씬 가늘어집니다.',

    // 사용법(프로세스)
    'home_process_label'       => '사용법',
    'home_process_title'       => '그린 것과 나온 것이 다르지 않게',
    'home_process_body'        => '설계와 제작 사이, 말로 옮겨 적는 단계가 없습니다. 완성한 도면 그대로 평목 공방에서 제작됩니다.',
    'home_guide_more'          => '가이드 전체 보기',

    'home_step1_title'         => '패턴 설계',
    'home_step1_desc'          => '평목 스튜디오는 브라우저에서 바로 사용할 수 있는 창호 설계 도구입니다. 상단 스튜디오 메뉴에서 원하는 창호 패턴을 선택하고, 문틀 크기·살 간격·패턴을 조정하며 나만의 창호를 완성해 보세요.',
    'home_step1_hint1'         => '문틀 가로·세로 크기 입력',
    'home_step1_hint2'         => '살 간격·두께 슬라이더 조정',
    'home_step1_hint3'         => '실시간으로 결과 확인',
    'home_step1_link'          => '스튜디오 가이드 보기',

    'home_step2_title'         => '저장 & 탐색',
    'home_step2_desc'          => '완성된 도면을 저장하고 컬렉션에서 영감을 찾아보세요.',
    'home_step2_hint1'         => '도면 저장 후 내 도면에서 관리',
    'home_step2_hint2'         => '컬렉션에서 다양한 패턴 탐색',
    'home_step2_hint3'         => '보드에 마음에 드는 패턴 모으기',
    'home_step2_link'          => '도면 관리 가이드 보기',

    'home_step3_title'         => '렌더링 & 내보내기',
    'home_step3_desc'          => '완성된 도면을 PNG·PDF·DXF로 내보내거나 AI 렌더링으로 실제 공간에 배치해 검토하세요.',
    'home_step3_hint1'         => 'PNG·PDF 고해상도 내보내기',
    'home_step3_hint2'         => 'DXF로 CAD 작업·정밀 치수 확인',
    'home_step3_hint3'         => 'AI 렌더링으로 공간 시각화',
    'home_step3_hint4'         => '배경 이미지와 도면 합성 확인',
    'home_step3_link'          => '렌더링 가이드 보기',

    'home_step4_title'         => '제작 주문',
    'home_step4_desc'          => '완성한 도면으로 주문하세요.',
    'home_step4_hint1'         => '도면 오른쪽 상단 견적요청 버튼 클릭',
    'home_step4_hint2'         => '저장한 도면 기반으로 상담',
    'home_step4_hint3'         => '공방 검토 후 최종 견적 회신',
    'home_step4_cta'           => '견적요청',
    'home_step4_link'          => '주문 안내 보기',

    // FAQ
    'home_faq_label'           => 'FAQ',
    'home_faq_title'           => '자주 묻는 질문',
    'home_faq_more'            => '전체 보기',

    // 블로그
    'home_blog_label'          => '블로그',
    'home_blog_title'          => '창호 이야기',
    'home_blog_body'           => '평목 공방이 전하는 창호와 한옥 살창 이야기.',
    'home_blog_more'           => '전체 보기',
    'home_blog_quote_read'     => '이야기 읽어보기',
    'home_blog_episode'        => '%d화',

    // Contact CTA
    'home_contact_label'       => 'Contact',
    'home_contact_title'       => '작은 문의도 괜찮습니다.',
    'home_contact_body'        => '설계·제작·설치 상담부터<br>협업 및 프로젝트 제안까지 모두 환영합니다.<br><br>편하게 연락해 주세요.<br>빠르게 답변드리겠습니다.',
    'home_contact_email_btn'   => '이메일 문의',
    'home_contact_hint'        => '평일 오전 10시 – 오후 6시 운영 · 주말·공휴일 이메일 접수 가능',
];
