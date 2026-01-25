<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>メインカレンダー</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .nav-grid {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-bottom: 20px;
        }

        .calendar-table td {
            position: relative;
            cursor: pointer;
            border: 1px solid #eee;
            transition: background-color 0.2s;
            height: 80px;
            vertical-align: top;
            padding: 5px;
            width: 14%;
        }

        .calendar-table td.today {
            border: 2px solid #2196F3 !important;
            font-weight: bold;
        }

        .calendar-table td.selected {
            background-color: #ffdae9 !important;
            color: #333 !important;
        }

        .coord-icon {
            font-size: 14px;
            display: block;
            margin-top: 5px;
            text-align: center;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }

        .primary-btn {
            display: inline-block;
            min-width: 180px;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: #555;
            background-color: transparent !important;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .primary-btn:hover {
            border-color: #2196F3;
            color: #2196F3;
            background-color: #f0f8ff !important;
        }
    </style>
</head>

<body>
    <div class="header-nav">
        <div class="calendar-nav">
            <a href="#" class="cal-nav-btn" id="prevMonthBtn">&lt;</a>
            <h1 id="calendarTitle">2026年1月</h1>
            <a href="#" class="cal-nav-btn" id="nextMonthBtn">&gt;</a>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <a href="#" id="logoutBtn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            ログアウト
        </a>
    </div>

    <div class="container">
        <div class="nav-grid">
            <a href="/main/closet_clothes" class="nav-item">
                <span class="nav-icon">👕</span><span>クローゼット管理</span>
            </a>
            <a href="/coord/add" class="nav-item">
                <span class="nav-icon">👗</span><span>コーデ管理</span>
            </a>
            <a href="/clothing/wear-screen" class="nav-item">
                <span class="nav-icon">👚</span><span>服管理</span>
            </a>
        </div>

        <table class="calendar-table">
            <thead>
                <tr>
                    <th class="cal-sun">日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th class="cal-sat">土</th>
                </tr>
            </thead>
            <tbody id="calendar-body"></tbody>
        </table>
        <hr>

        <div id="preview-area" class="preview-area">
            <h3 id="selected-date-display">日付を選択してください</h3>
            <div id="preview-content" style="text-align: center; margin: 10px 0; min-height: 100px;"></div>
            <div id="action-buttons" class="button-group"></div>
        </div>
    </div>

    <script>
        const DM = {
            async getMonthlyStatus(year, month) {
                const res = await fetch(`/api/calendar-status?year=${year}&month=${month}`);
                return await res.json();
            }
        };

        // 初期表示を現在の月に設定
        let currentDate = new Date();

        async function renderCalendar(date) {
            const calendarBody = document.getElementById("calendar-body");
            calendarBody.innerHTML = "";
            const year = date.getFullYear();
            const month = date.getMonth();
            document.getElementById("calendarTitle").innerText = `${year}年${month + 1}月`;

            const monthlyStatus = await DM.getMonthlyStatus(year, month + 1);
            const firstDay = new Date(year, month, 1).getDay();
            const lastDate = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            let dateCount = 1;
            for (let i = 0; i < 6; i++) {
                let row = document.createElement("tr");
                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement("td");
                    
                    if (i === 0 && j < firstDay || dateCount > lastDate) {
                        cell.innerText = "";
                        cell.style.backgroundColor = "#f9f9f9";
                        cell.style.cursor = "default";
                    } else {
                        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dateCount).padStart(2, '0')}`;
                        cell.innerText = dateCount;
                        cell.dataset.date = dateStr;

                        if (dateStr === todayStr) cell.classList.add("today");

                        // ▼ マス目はシンプルにアイコン表示（登録があれば）
                        if (monthlyStatus[dateStr] && (monthlyStatus[dateStr].isRegistered || monthlyStatus[dateStr].img)) {
                            const icon = document.createElement("div");
                            icon.className = "coord-icon";
                            icon.innerText = "👗";
                            cell.appendChild(icon);
                        }

                        cell.addEventListener("click", () => selectDate(dateStr, monthlyStatus[dateStr]));
                        dateCount++;
                    }
                    row.appendChild(cell);
                }
                calendarBody.appendChild(row);
                if (dateCount > lastDate) break;
            }
            autoSelectCurrentDay();
        }

        // ▼▼ 画像表示の修正箇所はここです ▼▼
        function selectDate(dateStr, status) {
            document.querySelectorAll(".calendar-table td").forEach(td => td.classList.remove("selected"));
            const target = document.querySelector(`td[data-date="${dateStr}"]`);
            if (target) target.classList.add("selected");

            document.getElementById("selected-date-display").innerText = dateStr;
            const actionContainer = document.getElementById("action-buttons");
            const previewContent = document.getElementById("preview-content");

            const editUrl = `/main/closet_edit?date=${dateStr}`;
            const checkUrl = `/main/closet_clothes?date=${dateStr}`;

            if (!status || (!status.isRegistered && !status.img)) {
                // まだ登録がない場合
                previewContent.innerHTML = `<p style="color:#999;">コーデは未登録です</p>`;
                actionContainer.innerHTML = `
                    <a href="${editUrl}" class="primary-btn">➕ コーデを登録する</a>
                `;
            } else if (!status.img) {
                // 登録はあるが画像がない場合
                previewContent.innerHTML = `<p style="color:#999;">全体像は未登録です</p>`;
                actionContainer.innerHTML = `
                    <a href="${checkUrl}" class="primary-btn">🔍 この日のコーデを確認</a>
                    <a href="${editUrl}" class="primary-btn">✏️ この日のコーデを変更</a>
                `;
            } else {
                // 画像がある場合（ここが修正ポイント！）
                // src に "/storage/" を付けることで画像が表示されます
                previewContent.innerHTML = `
                    <img src="/storage/${status.img}" style="max-height:200px; max-width:100%; border-radius:8px; border:1px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                `;
                actionContainer.innerHTML = `
                    <a href="${checkUrl}" class="primary-btn">🔍 この日のコーデを確認</a>
                    <a href="${editUrl}" class="primary-btn">✏️ この日のコーデを変更</a>
                `;
            }
        }
        // ▲▲ 修正箇所終わり ▲▲

        function autoSelectCurrentDay() {
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            const todayCell = document.querySelector(`td[data-date="${todayStr}"]`);
            if (todayCell) todayCell.click();
            else {
                // 今月表示で今日がない場合、1日を選択
                const firstCell = document.querySelector('td[data-date]');
                if (firstCell) firstCell.click();
            }
        }

        document.getElementById("prevMonthBtn").onclick = (e) => {
            e.preventDefault();
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        };

        document.getElementById("nextMonthBtn").onclick = (e) => {
            e.preventDefault();
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        };

        document.addEventListener("DOMContentLoaded", () => renderCalendar(currentDate));
    </script>
</body>

</html>