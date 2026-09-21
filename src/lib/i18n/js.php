<?php
// JS 파일용 사전 — 다른 사전과 달리 "한글 원문"을 키로 쓴다.
// .js는 PHP가 아니라 문구마다 키를 심기 어렵고 대상 파일이 많아(엔진 6종 등 500개 이상),
// 원문을 그대로 키로 두면 _t('저장되었습니다.') 형태로 기계적으로 감싸기만 하면 되고,
// 사전에 없는 문구는 한글 원문이 그대로 나와 화면이 깨지지 않는다.
// nav.php가 en 모드일 때만 이 배열을 window.PMOK_T로 내려보낸다.
// %s / %d 자리표시자는 JS의 _t(str, ...args)가 순서대로 치환한다.
return [
    // src/js/collection.js
    '검색 결과가 없습니다.'       => 'No results found.',
    '<strong>%s</strong>개 패턴'  => '<strong>%s</strong> patterns',
    '좋아요'                      => 'Like',
    '보드에 저장'                 => 'Save to board',
    '공유'                        => 'Share',
    '불러오는 중…'                => 'Loading…',
    '불러오기 실패'               => 'Failed to load',
    '아직 보드가 없습니다.'       => 'You have no boards yet.',
    '%s개 패턴'                   => '%s patterns',
    '"%s" 보드에 저장됐습니다.'   => 'Saved to the "%s" board.',
    '보드 생성 실패'              => 'Could not create the board',
    '"%s" 보드가 만들어졌습니다.' => 'Created the "%s" board.',

    '열기'                        => 'Open',

    // src/js/collection-share.js
    '평목 컬렉션'                 => 'Pyeongmok Collection',
    '복사됨'                      => 'Copied',
    '아래 링크를 복사하세요:'     => 'Copy the link below:',
    '평목 스튜디오에서 만든 문살 패턴을 확인해보세요.' => 'Check out this lattice pattern made in Pyeongmok Studio.',
];
