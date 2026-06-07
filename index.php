<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php require_once __DIR__ . '/src/lib/meta.php'; meta_tags(); ?>
        <?php define('BOOTSTRAP_LOADED', true); ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/src/css/index.css">
    </head>

    <body>

        <?php include __DIR__ . '/src/components/nav.php'; ?>

        <div class="home-wrapper">

            <!-- -->
            <div class="container">

                <div class="row g-4 mt-5">
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--classic h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/classic/classic.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g transform="rotate(90 340 340)">
                                            <rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="148" y="148" width="46" height="384" rx="0"/>
                                            <rect class="pm-symbol-bar" x="294" y="148" width="46" height="384" rx="0"/>
                                            <rect class="pm-symbol-bar" x="486" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Classic Grid</h4>
                            <p class="service-sub-text text-center mb-0">가로살과 세로살이 일정한 간격으로 교차하여 <br>정사각형 격자를 만드는 문살 형식</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--square h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/square/square.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/>
                                        <rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/>
                                        <rect class="pm-symbol-bar" x="204" y="148" width="46" height="384" rx="0"/>
                                        <rect class="pm-symbol-bar" x="430" y="148" width="46" height="384" rx="0"/>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Square Grid</h4>
                            <p class="service-sub-text text-center mb-0">가로살과 세로살이 일정한 간격으로 교차하여 <br>정사각형 격자를 만드는 문살 형식</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--cross h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/cross/cross.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g transform="rotate(45 340 340)">
                                            <rect class="pm-symbol-bar" x="148" y="204" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="148" y="430" width="384" height="46" rx="0"/>
                                            <rect class="pm-symbol-bar" x="204" y="148" width="46" height="384" rx="0"/>
                                            <rect class="pm-symbol-bar" x="430" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Cross Grid</h4>
                            <p class="service-sub-text text-center mb-0">정자살을 45도 회전하여 사선 방향으로 교차하는 <br>마름모 격자를 만드는 문살 형식</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--triangle h-80 p-4">
                            <div class=" mb-4">
                                <a href="/src/engine/sambuntok/sambuntok.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        <g transform="rotate(60 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                        <g transform="rotate(120 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Triangle Grid</h4>
                            <p class="service-sub-text text-center mb-0">사선으로 교차하는 문살을 기반으로 구성된 패턴으로, 직선 격자보다 역동적이고 세련된 인상을 제공합니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card service-card--diamond h-80 p-4">
                            <div class="text-center mb-4">
                                <a href="/src/engine/Sabunteok/Sabunteok.php" class="pm-symbol-link">
                                    <svg
                                        width="120"
                                        height="120"
                                        viewbox="0 0 680 680"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        <rect class="pm-symbol-bar" x="148" y="317" width="384" height="46" rx="0"/>
                                        <g transform="rotate(45 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                        <g transform="rotate(135 340 340)">
                                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                                        </g>
                                    </svg>
                                </a>
                            </div>
                            <h4 class="service-title text-center mb-3">Diamond Grid</h4>
                            <p class="service-sub-text text-center mb-0">Transform your data into actionable insights with our advanced analytics solutions.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- card -->
            <section class="values-section mt-5">
                <div class="values-inner container">
                    <div class="values-grid">
                        <div class="values-item">
                            <h2 class="values-title">당신의 공간에 빛과 바람의 길을 디자인하세요.</h2>
                            <p class="sub-title">문틀 크기와 비율을 조정하면 실제 제작 가능한<br>맞춤 창호 디자인을 실시간으로 확인할 수 있습니다</p>
                        </div>
                        <div class="values-item">
                            <h2 class="values-title">나만의 창호를 직접 디자인하세요.</h2>
                            <p class="sub-title">실시간으로 디자인하고, 공방에서 완성합니다.<br>원하는 디자인을 즉시 확인하고
                                <br>완성된 설계는 실제 제작으로 이어집니다.
                            </p>
                        </div>
                    </div>
                    <hr>
                </div>
            </section>
            <!-- card -->
        </div>
        <!-- home-wrapper -->

        <!-- FOOTER -->
        <div
            class="site-footer border-top d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 px-4 py-4">
            <p class="footer-copy mb-0">©
                <?= date('Y') ?>
                평목(平木). All rights reserved.</p>
            <a href="https://pyeongmok.com" class="footer-link">pyeongmok.com</a>
        </div>

    </body>

</html>