<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>コーデ削除画面</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .coord-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .coord-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .coord-title {
            font-size: 1.1em;
            font-weight: bold;
            margin: 0;
        }
        .favorite-mark {
            color: #e91e63;
            font-size: 1.2em;
        }
        
        /* 服リストのスタイル */
        .wear-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .wear-item {
            width: 80px;
            text-align: center;
        }
        .wear-item img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border: 1px solid #eee;
            border-radius: 4px;
            background-color: #fafafa;
        }
        .wear-name {
            font-size: 10px;
            margin-top: 4px;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 全体画像がある場合 */
        .main-image {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
            display: block;
        }

        .delete-form {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header-nav">
        <h1>コーデ削除 (選択)</h1>
        <a href="{{ route('coord.manage') }}">コーデ管理へ戻る</a>
    </div>

    <div class="container">
        <p>削除するコーデの「削除」ボタンを押してください。<br>※一度削除すると元に戻せません。</p>

        @if(session('success'))
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        @forelse($coords as $coord)
            <div class="coord-card">
                <div class="coord-header">
                    <h3 class="coord-title">
                        {{ $coord->CODE_NAME ?? '名称未設定' }}
                    </h3>
                    @if($coord->IS_FAVORITE)
                        <span class="favorite-mark">❤</span>
                    @endif
                </div>

                @if($coord->IMAGE_PATH)
                    <img src="{{ asset('storage/' . $coord->IMAGE_PATH) }}" alt="全体画像" class="main-image">
                @endif

                <div class="delete-form">
                    <form action="{{ route('coord.destroy', $coord->CODE_ID) }}" method="POST" onsubmit="return confirm('本当にこのコーデを削除してもよろしいですか？\n登録しているクローゼットからも消える可能性があります。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button danger">削除する</button>
                    </form>
                </div>
            </div>
        @empty
            <p style="text-align: center; color: #777; margin-top: 30px;">
                登録されているコーデはありません。
            </p>
        @endforelse

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route('coord.manage') }}" class="button">キャンセル（戻る）</a>
        </div>
    </div>
</body>
</html>