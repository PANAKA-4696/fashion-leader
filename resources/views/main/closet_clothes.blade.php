<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <title>今日のコーデ確認</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-action {
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            text-decoration: none;
            color: white;
            background-color: #4CAF50;
        }

        .btn-delete {
            background-color: #E53935;
        }
    </style>
</head>

<body>
    <div class="header-nav">
        <h1>今日のコーデ確認</h1>
        <a href="/main/calendar">カレンダーへ</a>
    </div>

    <div class="container">
        <div class="date-display-container">
            <h2 id="view-date" class="view-date-display">----年--月--日のコーデ</h2>
        </div>

        <p class="item-line">
            <span class="label" style="font-weight: bold; color: #c62828;">全体像:</span>
            <span class="img-box">
                <img id="overall_photo" class="clothing-img" src="" alt="コーデ全体像">
            </span>
            <p id="overall_none" style="color:#999; display:none;">全体像は未登録です</p>
        </p>

        <hr>

        <div id="items-list">
            <p class="item-line"><span class="label">シャツ:</span> <span class="img-box"><img id="item-shirt" class="clothing-img" src=""></span></p>
            <p class="item-line"><span class="label">パンツ:</span> <span class="img-box"><img id="item-pants" class="clothing-img" src=""></span></p>
            <p class="item-line"><span class="label">シューズ:</span> <span class="img-box"><img id="item-shoes" class="clothing-img" src=""></span></p>
        </div>

        <div class="button-group">
            <button id="editBtn" class="btn-action">このコーデを変更する</button>
            <button id="deleteBtn" class="btn-action btn-delete">このコーデを削除する</button>
        </div>
    </div>

    <script>
        const DM = {
            async getCoordData(date) {
                const res = await fetch(`/api/coord?date=${date}`);
                return await res.json();
            },

            async deleteCoord(date) {
                await fetch('/main/deleteCoord', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ date })
                });

                alert("削除が完了しました");
                window.location.href = "/main/calendar";
            }
        };

        async function initDisplay() {
            const params = new URLSearchParams(window.location.search);
            const dateStr = params.get('date');

            if (!dateStr) {
                alert("日付情報がありません");
                window.location.href = "/main/calendar";
                return;
            }

            document.getElementById("view-date").innerText = `${dateStr} のコーデ`;

            const data = await DM.getCoordData(dateStr);

            const overallImg = document.getElementById("overall_photo");
            const overallNone = document.getElementById("overall_none");

            if (!data) {
                overallImg.style.display = "none";
                overallNone.style.display = "block";
            } else if (!data.overall) {
                overallImg.style.display = "none";
                overallNone.style.display = "block";
            } else {
                overallImg.style.display = "block";
                overallNone.style.display = "none";
                overallImg.src = data.overall;
            }

            document.getElementById("item-shirt").src = data?.shirt ?? "";
            document.getElementById("item-pants").src = data?.pants ?? "";
            document.getElementById("item-shoes").src = data?.shoes ?? "";

            document.getElementById("editBtn").onclick = () => {
                window.location.href = `/main/closet_edit?date=${dateStr}`;
            };

            document.getElementById("deleteBtn").onclick = (e) => {
                e.preventDefault();
                if (confirm("この日のコーデを削除してもよろしいですか？")) {
                    DM.deleteCoord(dateStr);
                }
            };
        }

        document.addEventListener("DOMContentLoaded", initDisplay);
    </script>
</body>
</html>
