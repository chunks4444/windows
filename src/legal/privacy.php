<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php css_tag('/src/css/legal.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="legal-page">
    <h1>개인정보처리방침</h1>
    <p class="legal-updated">시행일자: 2026년 7월 15일</p>

    <p>
        평목(平木, 이하 "회사")은 이용자의 개인정보를 중요시하며, 「개인정보보호법」 등
        관련 법령을 준수합니다. 회사는 본 개인정보처리방침을 통해 이용자가 제공하는
        개인정보가 어떤 목적과 방식으로 이용되고 있으며, 개인정보보호를 위해 어떤 조치가
        취해지고 있는지 알려드립니다.
    </p>

    <h2>1. 수집하는 개인정보 항목</h2>
    <p>회사는 회원가입, 서비스 이용, 주문·상담 과정에서 아래와 같은 개인정보를 수집합니다.</p>
    <ul>
        <li><strong>회원가입 시(필수)</strong>: 이메일 주소, 비밀번호(자체 가입) 또는 소셜 로그인 식별 정보(구글·카카오·네이버 계정 이메일)</li>
        <li><strong>주문·제작 의뢰 시(필수)</strong>: 이름, 연락처, 배송지 주소</li>
        <li><strong>문의·상담 시(선택)</strong>: 이름, 연락처, 이메일 주소</li>
        <li><strong>서비스 이용 과정에서 자동 수집</strong>: 접속 IP, 쿠키, 방문 일시, 서비스 이용 기록, 브라우저·기기 정보</li>
    </ul>

    <h2>2. 개인정보 수집 방법</h2>
    <ul>
        <li>홈페이지 회원가입, 소셜 로그인(OAuth) 인증</li>
        <li>주문·제작 의뢰, 이메일 문의 접수 시 이용자가 직접 입력</li>
        <li>서비스 이용 과정에서 쿠키를 통해 자동 생성·수집</li>
    </ul>

    <h2>3. 개인정보의 수집 및 이용 목적</h2>
    <ul>
        <li>회원 식별, 가입 의사 확인, 부정 이용 방지</li>
        <li>맞춤 제작 주문 접수·제작·배송 및 사후 문의 응대</li>
        <li>문의·상담에 대한 답변</li>
        <li>서비스 이용 통계 분석 및 서비스 개선 (Google Analytics)</li>
    </ul>

    <h2>4. 개인정보의 보유 및 이용 기간</h2>
    <p>
        회사는 원칙적으로 개인정보 수집·이용 목적이 달성되거나 회원 탈퇴 시 해당 정보를
        지체 없이 파기합니다. 다만 관계 법령에 따라 보존할 필요가 있는 경우, 회사는
        아래와 같이 관계 법령에서 정한 일정한 기간 동안 회원정보를 보관합니다.
    </p>
    <table>
        <tr><th>보관 항목</th><th>보관 기간</th><th>근거 법령</th></tr>
        <tr><td>계약 또는 청약철회 등에 관한 기록</td><td>5년</td><td>전자상거래 등에서의 소비자보호에 관한 법률</td></tr>
        <tr><td>대금결제 및 재화 등의 공급에 관한 기록</td><td>5년</td><td>전자상거래 등에서의 소비자보호에 관한 법률</td></tr>
        <tr><td>소비자 불만 또는 분쟁처리에 관한 기록</td><td>3년</td><td>전자상거래 등에서의 소비자보호에 관한 법률</td></tr>
        <tr><td>접속에 관한 기록</td><td>3개월</td><td>통신비밀보호법</td></tr>
    </table>

    <h2>5. 개인정보의 제3자 제공</h2>
    <p>
        회사는 이용자의 개인정보를 원칙적으로 외부에 제공하지 않습니다. 다만 아래의
        경우에는 예외로 합니다.
    </p>
    <ul>
        <li>이용자가 사전에 제3자 제공에 동의한 경우</li>
        <li>법령의 규정에 의거하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우</li>
    </ul>

    <h2>6. 개인정보처리 위탁</h2>
    <p>회사는 원활한 서비스 제공을 위해 아래와 같이 개인정보 처리를 위탁하고 있습니다.</p>
    <table>
        <tr><th>수탁업체</th><th>위탁업무 내용</th></tr>
        <tr><td>Google LLC</td><td>소셜 로그인(OAuth) 인증, 웹사이트 이용 통계 분석(Google Analytics)</td></tr>
        <tr><td>Kakao Corp.</td><td>소셜 로그인(OAuth) 인증</td></tr>
        <tr><td>NAVER Corp.</td><td>소셜 로그인(OAuth) 인증</td></tr>
    </table>

    <h2>7. 이용자 및 법정대리인의 권리와 행사 방법</h2>
    <p>
        이용자는 언제든지 마이페이지 또는 이메일 문의를 통해 본인의 개인정보를
        조회·수정하거나 회원 탈퇴(수집·이용 동의 철회)를 요청할 수 있습니다. 회사는
        관계 법령에 특별한 규정이 없는 한 지체 없이 해당 요청을 처리합니다.
    </p>

    <h2>8. 쿠키(Cookie)의 운영 및 거부</h2>
    <p>
        회사는 로그인 상태 유지, 서비스 이용 통계 분석(Google Analytics)을 위해 쿠키를
        사용합니다. 이용자는 브라우저 설정을 통해 쿠키 저장을 거부할 수 있으며, 이
        경우 로그인이 필요한 일부 서비스 이용에 제한이 있을 수 있습니다.
    </p>

    <h2>9. 개인정보의 파기절차 및 방법</h2>
    <p>
        회사는 개인정보 보유 기간의 경과, 처리 목적 달성 등 개인정보가 불필요하게
        되었을 때에는 지체 없이 해당 개인정보를 파기합니다. 전자적 파일 형태의 정보는
        복구·재생이 불가능한 방법으로 삭제하며, 종이에 출력된 개인정보는 분쇄하거나
        소각하여 파기합니다.
    </p>

    <h2>10. 개인정보 보호책임자</h2>
    <p>
        회사는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한
        이용자의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를
        지정하고 있습니다.
    </p>
    <ul>
        <li>연락처(이메일): <span id="legalEmail"></span></li>
        <li>연락처(전화): <a href="tel:+827051244568">070-5124-4568</a></li>
        <li>주소: 경기도 양평군 양서면 도곡리 107-2</li>
    </ul>

    <h2>11. 고지의 의무</h2>
    <p>
        현 개인정보처리방침의 내용 추가, 삭제 및 수정이 있을 시에는 개정 최소 7일
        전부터 홈페이지의 공지사항을 통해 고지할 것입니다.
    </p>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script>
(function () {
    var u = 'pyeongmok', d = 'gmail.com', e = u + '@' + d;
    var el = document.getElementById('legalEmail');
    if (!el) return;
    var a = document.createElement('a');
    a.href = 'mailto:' + e;
    a.textContent = e;
    el.appendChild(a);
})();
</script>
</body>
</html>
