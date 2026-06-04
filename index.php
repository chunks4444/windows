<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta
            name="description"
            content="평목은 전통창호, 한옥창호, 목창호, 맞춤창호를 전문으로 하는 창호디자인 스튜디오입니다. 공장에서 담아낼 수 없는 공간의 깊이를 나무문과 나무창문으로 표현합니다.">
        <meta
            name="keywords"
            content="전통창호, 목창호, 한옥창호, 맞춤창호, 창호디자인, 나무창호, 한옥문, 나무문, 전통문, 평목">
        <link
            href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;600;700;900&family=Inter:wght@300;400;500&display=swap"
            rel="stylesheet">
        <?php define('BOOTSTRAP_LOADED', true); ?>
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet">
        <link rel="stylesheet" href="/src/css/index.css">
    </head>

    <body>

        <?php include __DIR__ . '/src/components/nav.php'; ?>

        <div class="home-wrapper">

            <!-- -->
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-12 text-center mb-5">

                        <span class="custom-badge text-white mb-3 d-inline-block">Our Services</span>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <div class="service-card h-80 p-4" style="background-image: url(https://i.pinimg.com/736x/eb/eb/ba/ebebbadeff7623b9372b42c52f01a5de.jpg);">
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
                            <h4 class="service-title text-center mb-3">Square Grid</h4>
                            <p class="service-sub-text text-center mb-0">가로살과 세로살이 일정한 간격으로 교차하여 <br>정사각형 격자를 만드는 문살 형식</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card h-80 p-4" style="background-image: url(https://i.pinimg.com/736x/eb/39/7c/eb397c9ef2482d34fdbfe0c5aaafffd3.jpg);">
                            <div class=" mb-4" >
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
                            <p class="service-sub-text  text-center mb-0">사선으로 교차하는 문살을 기반으로 구성된 패턴으로, 직선 격자보다 역동적이고 세련된 인상을 제공합니다.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-card h-80 p-4" style="background-image: url(https://i.pinimg.com/736x/0f/1a/f2/0f1af248ae79652e0b6b388b7ddb871d.jpg);">
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
                            <p class="service-sub-text  text-center mb-0">Transform your data into actionable
                                insights with our advanced analytics solutions.</p>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .service-card {
                    transition: all 0.3s ease;
                    border: none;
                    border-radius: 15px;
                    overflow: hidden;
                    background: linear-gradient(145deg, #ffffff, #f3f3f3);
                    box-shadow: 5px 5px 15px #d9d9d9, -5px -5px 15px #ffffff;
                }

                .service-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 8px 8px 20px #d1d1d1, -8px -8px 20px #ffffff;
                }

                .icon-wrapper {
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto;
                    background: linear-gradient(135deg, #6366f1, #8b5cf6);
                }

                .custom-badge {
                    background: linear-gradient(135deg, #6366f1, #8b5cf6);
                    padding: 8px 15px;
                    border-radius: 20px;
                    font-size: 0.8rem;
                    font-weight: 500;
                }

                .service-title {
                    color: #ffffff;
                    font-weight: 600;
                }

                .service-text {
                    color: #ffffff;
                    font-size: 0.95rem;
                }
            </style>

            <!-- card -->
            <section class="values-section mt-5">
                <div class="values-inner">
                    <div class="values-grid">
                        <div class="values-item">
                            <h2 class="values-title">당신의 공간에 빛과 바람의 길을 디자인하세요.</h2>
                            <p class="sub-title">문틀 크기와 비율을 조정하면 실제 제작 가능한<br>맞춤 창호 디자인을 실시간으로 확인할 수 있습니다</p>
                        </div>
                        <div class="values-item">
                            <h2 class="values-title">DESIGN IN REAL TIME</h2>
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
            class="border-top d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 px-4 py-4">
            <p class="footer-copy mb-0">©
                <?= date('Y') ?>
                평목(平木). All rights reserved.</p>
            <a href="https://pyeongmok.com" class="footer-link">pyeongmok.com</a>
        </div>

    </body>

</html>