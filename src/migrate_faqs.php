<?php
require_once __DIR__ . '/lib/db.php';
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS faqs (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    question    VARCHAR(255)      NOT NULL DEFAULT '',
    answer      TEXT              NOT NULL,
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    created_at  DATETIME          NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    KEY idx_faqs_sort (sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='자주 묻는 질문'
");

$pdo->exec("
INSERT IGNORE INTO faqs (id, question, answer, sort_order) VALUES
(1, '회원가입 없이도 스튜디오를 사용할 수 있나요?', '네, 회원가입 없이도 스튜디오에서 창호를 자유롭게 설계하고 미리보기를 확인할 수 있습니다. 다만 도면 저장, AI 렌더링, 컬렉션 보드 등의 기능은 로그인 후 이용 가능합니다.', 0),
(2, '창호 크기와 살 간격을 직접 설정할 수 있나요?', '네, 문틀 가로·세로 크기, 살 간격, 살 두께를 슬라이더와 입력창으로 직접 조정할 수 있습니다. 변경 사항은 캔버스에 실시간으로 반영됩니다.', 1),
(3, '도면을 PDF나 이미지로 내보낼 수 있나요?', '네, 스튜디오 상단의 내보내기 버튼을 통해 PNG 이미지와 PDF 파일로 다운로드할 수 있습니다. 제작 의뢰 시 이 파일을 활용하시면 됩니다.', 2),
(4, 'AI 렌더링은 어떻게 사용하나요?', '스튜디오에서 도면을 완성한 후 AI 렌더링 버튼을 클릭하면, 원하는 배경 이미지와 창호 도면을 합성해 공간에 적용된 모습을 AI로 시각화해 드립니다.', 3),
(5, '제작 주문은 어떻게 하나요?', '스튜디오에서 완성된 도면의 오른쪽 상단 주문 버튼을 클릭하시거나, 하단 함께 만들어가요 링크를 통해 평목 공방에 제작 상담을 신청하실 수 있습니다.', 4)
");

echo "완료: faqs 테이블 생성 및 초기 데이터 삽입 성공";
