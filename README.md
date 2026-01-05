# 👕 Webアプリ開発環境 セットアップ手順（Windows）

本手順書は、チーム開発に必要なWebアプリ開発環境をWindows上に構築するためのガイドです。
以下の手順通りに進めれば、全員が同じ開発環境を構築できます。

---

## 🛠 手順1：PCの準備（WSL2 と Docker）

### 1-1. Ubuntu（WSL2）のインストール

Windows上でLinux環境を動かすために WSL2 を使用します。

1. Windowsのスタートボタンを右クリック  
2. 「ターミナル（管理者）」または「PowerShell（管理者）」を起動  
3. 以下のコマンドを実行します

```powershell
wsl --install
```

4. 処理完了後、必ずPCを再起動してください  
5. 再起動後、自動的に Ubuntu（黒い画面）が起動します  
6. 以下を順に入力します  
   - Enter new UNIX username: → 任意のユーザー名（英数字）  
   - New password: → パスワード  

※ パスワード入力時は画面に何も表示されませんが、正常に入力されています。

---

### 1-2. Docker Desktop のインストール

1. [Docker公式サイト](https://www.docker.com/products/docker-desktop/)から Download for Windows を選択  
2. インストーラーを起動し、すべてデフォルト設定のままインストール  
3. インストール完了後、Docker Desktop を起動  

---

### 1-3. Docker と Ubuntu（WSL）の連携（重要）

1. Docker Desktop を起動  
2. 右上の Settings（歯車）をクリック  
3. Resources → WSL Integration を選択  
4. 以下を確認  
   - Enable integration with my default WSL distro にチェック  
   - Ubuntu のスイッチが ON（青）  
5. Apply & Restart をクリック  

---

## 🚀 手順2：プロジェクトのダウンロードと起動

※ ここからは Ubuntu（黒い画面）を使用します

### 2-1. プロジェクトの取得（Git Clone）

```bash
git clone https://github.com/PANAKA-4696/fashion-leader.git
cd fashion-leader
```

---

### 2-2. ライブラリのインストール（初回のみ）

```bash
docker run --rm -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php82-composer:latest \
  composer install --ignore-platform-reqs
```

---

### 2-3. 設定ファイルの準備

```bash
cp .env.example .env
```

---

### 2-4. アプリケーションの起動

```bash
./vendor/bin/sail up -d
```

---

### 2-5. 初期設定（キー生成 & DB構築）

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

---

## ✅ 手順3：動作確認

ブラウザで以下にアクセスします。

```
http://localhost
```

Laravelのトップページが表示されれば成功です。

---

## 📝 開発の進め方メモ

### 開発を始めるとき

```bash
./vendor/bin/sail up -d
```

### 開発を終わるとき

```bash
./vendor/bin/sail stop
```

### VSCodeで編集する場合

```bash
code .
```

Ubuntu上で実行すると Windows 側の VSCode が起動します。
