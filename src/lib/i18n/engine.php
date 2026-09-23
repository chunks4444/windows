<?php
// 엔진 페이지(src/engine/{type}/{type}.php) 사이드바·툴바 문구 사전.
// js.php와 같은 이유로 "한글 원문"을 키로 쓴다 — 엔진 6종이 서로 95% 같은 사이드바를 복사해 쓰고 있어
// 문구마다 키를 새로 지으면 149개 키를 6곳에 심어야 하지만, 원문을 키로 두면 te('문틀 가로')처럼
// 기계적으로 감싸기만 하면 된다. 사전에 없는 문구는 한글이 그대로 나와 화면이 깨지지 않는다.
//
// 번역어는 이미 영문화가 끝난 가이드(src/guide/en/)의 표현을 그대로 따른다.
// 가이드에서 Frame width / Slat thickness / Production specification 등으로 이미 쓰고 있으므로
// 엔진 UI가 다른 낱말을 쓰면 사용자가 가이드를 봐도 같은 항목인지 알 수 없게 된다.
// 새 문구를 추가할 때도 먼저 src/guide/en/에서 같은 항목이 어떻게 불리는지 찾아볼 것.
//
// title 속성 안의 &#10;은 줄바꿈이다. 번역문에서도 같은 자리에 그대로 남겨야 툴팁이 여러 줄로 나온다.
return [
    // ── 문 설정 ─────────────────────────────────────
    '문 설정'                 => 'Door settings',
    '여닫이'                  => 'Hinged',
    '미서기'                  => 'Sliding',
    '1짝'                     => '1 panel',
    '2짝'                     => '2 panels',
    '3짝'                     => '3 panels',
    '4짝'                     => '4 panels',
    '6짝'                     => '6 panels',
    '문틀 가로'               => 'Frame width',
    '문틀 세로'               => 'Frame height',
    '전체 문폭'               => 'Total door width',
    '겹침'                    => 'Overlap',

    // ── 살 설정 ─────────────────────────────────────
    '가로 칸수'               => 'Horizontal cells',
    '세로 칸수'               => 'Vertical cells',
    '세로 비율'               => 'Vertical ratio',
    '세로 자동 맞춤'          => 'Auto-fit vertically',
    '세로 내경중심'           => 'Vertical inner center',
    '살 두께'                 => 'Slat thickness',
    '좌우 울거미 두께'        => 'Left/right stile thickness',
    '상하 울거미 두께'        => 'Top/bottom rail thickness',
    '가로살 · 세로살'         => 'Horizontal · vertical slats',
    '가로살 개수'             => 'Horizontal slat count',
    '가로살 개수 직접 지정'   => 'Set horizontal slat count manually',
    // 정자살 3구간 살 개수. 세 칸이 나란히 놓이므로 낱말이 길면 사이드바를 넘친다 —
    // 머리말(가로살)을 위로 빼고 칸에는 위치만 두는 구조라 짧게 유지할 것.
    '가로살'                  => 'Horizontal slats',
    '상단'                    => 'Top',
    '중단'                    => 'Middle',
    '하단'                    => 'Bottom',
    '사선살'                  => 'Diagonal slats',
    '사선 간격'               => 'Diagonal spacing',
    '변 간격'                 => 'Side spacing',
    '패턴 세로 방향'          => 'Pattern vertical direction',
    '랜덤 패턴'               => 'Random pattern',
    '랜덤 생성'               => 'Generate random',
    '풍판'                    => 'Transom panel',
    '풍판 사용'               => 'Use transom panel',
    '풍판 높이'               => 'Transom panel height',
    '치수 표기'               => 'Show dimensions',
    '문틀 표시'               => 'Show door frame',

    // ── 컬러 ────────────────────────────────────────
    '살 컬러'                 => 'Slat color',
    '울거미 컬러'             => 'Stile & rail color',
    '문틀 컬러'               => 'Frame color',
    '면 컬러'                 => 'Face color',
    '면컬러 칠하기'           => 'Paint face color',
    '색상'                    => 'Color',
    '투명도'                  => 'Opacity',
    '채우기'                  => 'Fill',

    // ── 제작 시방서 ─────────────────────────────────
    '제작 시방서'             => 'Production specification',
    '외경 가로'               => 'Outer width',
    '외경 세로'               => 'Outer height',
    '내경 가로'               => 'Inner width',
    '내경 세로'               => 'Inner height',
    '칸'                      => 'cells',
    '부재 목록'               => 'Member list',
    '폭×두께×길이'            => 'W × T × L',
    '가로부재'                => 'Horizontal members',
    '세로부재'                => 'Vertical members',
    '가로 먹줄'               => 'Horizontal ink lines',
    '세로 먹줄'               => 'Vertical ink lines',
    '사선 먹줄'               => 'Diagonal ink lines',
    '살 먹줄'                 => 'Slat ink lines',
    '반턱 너비'               => 'Half-lap width',
    '울거미홈폭'              => 'Stile groove width',
    '가로울거미홈폭'          => 'Horizontal stile groove width',
    '세로울거미홈폭'          => 'Vertical stile groove width',
    '세로울거미홈간격'        => 'Vertical stile groove spacing',
    '울거미'                  => 'Stiles & rails',
    '상/하 울거미'            => 'Top/bottom rails',
    '살'                      => 'Slats',

    // ── 원가·견적 ───────────────────────────────────
    '예상가격'                => 'Estimated price',
    '목재비'                  => 'Timber cost',
    '제작비'                  => 'Labor cost',
    '간접비'                  => 'Overhead',
    '이익'                    => 'Profit',
    '판매가'                  => 'Price',
    '최소 납기'               => 'Minimum lead time',
    '일'                      => ' days',
    '문(창호) 목재'           => 'Door (changho) timber',
    '문틀 목재'               => 'Door frame timber',
    '문틀'                    => 'Door frame',
    '마감'                    => 'Finish',
    '마감 없음'               => 'No finish',
    '부자재'                  => 'Hardware',
    '부자재 없음'             => 'No hardware',
    '견적요청'                => 'Request quote',
    '※ 배송비·시공비 제외'    => '※ Excludes shipping and installation',
    '※ 본 금액은 예상 견적입니다. 사용자 편집 내용을 검토한 후 최종 견적이 확정됩니다.'
        => '※ This is an estimate. The final quote is confirmed after we review your edits.',

    // ── 도면 관리 바 ────────────────────────────────
    '도면'                    => 'Drawing',
    '도면 #—'                 => 'Drawing #—',
    '도면 목록'               => 'Drawings',
    '도면 이름 입력…'         => 'Enter drawing name…',
    '새 도면'                 => 'New drawing',
    '도면 제목 변경'          => 'Rename drawing',
    '새 제목 입력…'           => 'Enter a new title…',
    '저장'                    => 'Save',
    '변경'                    => 'Rename',
    '복사'                    => 'Copy',
    '삭제'                    => 'Delete',
    '적용'                    => 'Apply',
    // js.php에도 '취소'가 있지만 거기는 주문 '상태'(Cancelled)고 여기는 '버튼'(Cancel)이다.
    // 사전이 분리돼 있어 서로 간섭하지 않는다 — 같게 맞추려 하지 말 것.
    '취소'                    => 'Cancel',
    '닫기'                    => 'Close',
    '초기화'                  => 'Reset',
    '먼저 저장해주세요'       => 'Please save first',
    '로그인하고 내 도면함에 복사' => 'Sign in and copy to My Drawings',

    // ── 공유 ────────────────────────────────────────
    '공유'                    => 'Share',
    '공유 끄기'               => 'Turn off sharing',
    '카카오'                  => 'Kakao',
    '카카오톡 공유'           => 'Share on KakaoTalk',
    '페이스북 공유'           => 'Share on Facebook',
    'X(트위터) 공유'          => 'Share on X (Twitter)',

    // ── 내보내기·렌더링 ─────────────────────────────
    '내보내기'                => 'Export',
    '렌더링'                  => 'Rendering',
    'AI 렌더링 중…'           => 'AI rendering…',
    '재질/조명 선택…'         => 'Choose material / lighting…',
    '프리셋을 선택하거나 직접 입력하세요' => 'Choose a preset or type your own',
    '미리보기 이미지 없음'    => 'No preview image',

    // ── 문양·도형·배경 ──────────────────────────────
    '문양 삽입'               => 'Insert motif',
    '문양 라이브러리'         => 'Motif library',
    '라이브러리'              => 'Library',
    '업로드'                  => 'Upload',
    '패턴 분류'               => 'Pattern category',
    '분류 없음'               => 'No category',   // js.php(마이페이지 도면목록)와 같은 낱말로 맞춤
    '도형'                    => 'Shapes',
    '도형 모두 삭제'          => 'Delete all shapes',
    '선'                      => 'Line',
    '두께'                    => 'Thickness',
    '크기'                    => 'Size',
    '회전'                    => 'Rotate',
    '이동'                    => 'Move',
    '배경 업로드'             => 'Upload background',
    '배경 지우기'             => 'Clear background',
    '배경사진 패널 열기/닫기' => 'Open/close background photo panel',
    '배치 초기화'             => 'Reset placement',
    '치수창 열기/닫기'        => 'Open/close dimensions panel',
    '화면 초기화'             => 'Reset view',
    '확대'                    => 'Zoom in',
    '축소'                    => 'Zoom out',
    '스케일/이동/변형'        => 'Scale / move / transform',
    '1개'                     => '×1',
    '2개'                     => '×2',

    // ── 엔진 좌하단 블로그 링크 (src/components/blog_engine_link.php) ──
    '이 살의 이야기'          => 'The story of this lattice',

    // ── 견적요청 모달 (src/components/order_modal.php) ──
    // '요청사항'은 마이페이지 주문내역(js.php)에서 이미 Request notes로 쓰고 있어 같은 낱말로 맞춘다.
    '견적요청하기'            => 'Send request',
    '도면 미리보기'           => 'Drawing preview',
    '요청일'                  => 'Request date',
    '요청자'                  => 'Requested by',
    '버전'                    => 'Version',
    '연락처'                  => 'Phone',
    '회사명'                  => 'Company',
    '납기 희망일'             => 'Requested delivery date',
    '배송지'                  => 'Delivery address',
    '배송지 연락처'           => 'Delivery phone',
    '우편번호'                => 'Postal code',
    '주소'                    => 'Address',
    '상세주소'                => 'Address line 2',
    '검색'                    => 'Search',
    '요청사항'                => 'Request notes',
    '요청사항을 입력해주세요 (선택)' => 'Add any notes for us (optional)',

    // ── 캔버스 도구 툴팁 (&#10; = 줄바꿈, 자리 유지) ──
    '선택&#10;살 클릭 → 색상·삭제&#10;도형 클릭 → 이동·크기조절·회전'
        => 'Select&#10;Click a slat → color · delete&#10;Click a shape → move · resize · rotate',
    '선 그리기&#10;시작점 클릭 → 끝점 클릭'
        => 'Draw line&#10;Click the start point → click the end point',
    '사각형 그리기&#10;클릭 → 사각형 배치'
        => 'Draw rectangle&#10;Click → place a rectangle',
    '원 그리기&#10;클릭 → 원 배치'
        => 'Draw circle&#10;Click → place a circle',
    '텍스트 추가&#10;클릭 → 텍스트 입력'
        => 'Add text&#10;Click → type the text',
    '선 추가&#10;① 시작 교점 클릭&#10;② 끝 교점 클릭 → 선 완성'
        => 'Add line&#10;① Click the start intersection&#10;② Click the end intersection',
    '선 추가 ① 시작 교점 클릭 ② 끝 교점 클릭 → 선 완성'
        => 'Add line — ① click the start intersection ② click the end intersection',
    '선 삭제&#10;클릭 → 선 삭제&#10;다시 클릭 → 복구'
        => 'Delete line&#10;Click → delete the line&#10;Click again → restore it',
    '선 삭제 클릭 → 선 삭제 다시 클릭 → 복구'
        => 'Delete line — click to delete, click again to restore',
    '편집 초기화&#10;모든 삭제·추가 선 초기화'
        => 'Reset edits&#10;Clears every deleted and added line',
    '편집 초기화 모든 삭제·추가 선 초기화'
        => 'Reset edits — clears every deleted and added line',
];
