# 員工班表管理系統

以網頁為媒介，建立一個適合醫美診所的員工班表系統，透過符合需求和精心設計的UI設計，讓排班表、讀班表變得更輕鬆。

## 專案結構

```
ecume/
├── backend/          # Laravel 後端 API
├── frontend/         # Vue 3 前端
├── docker/          # Docker 環境配置
└── docker-compose.yml
```

## 技術棧

### 後端
- Laravel 10.x
- MySQL 8.0
- PHP 8.2

### 前端
- Vue 3
- Vite
- Tailwind CSS
- Axios

## 快速開始

### 1. 啟動 Docker 容器

```bash
docker-compose up -d
```

### 2. 安裝後端依賴並執行 Migration

```bash
# 進入 PHP 容器
docker-compose exec php bash

# 安裝依賴
composer install

# 執行 migration
php artisan migrate

# 執行 seeder（建立測試資料）
php artisan db:seed
```

### 3. 安裝前端依賴並啟動開發服務器

前端會自動透過 docker-compose 啟動，無需手動執行。

### 4. 訪問應用

- **前端開發服務器**: http://localhost:5173
- **後端 API**: http://localhost:8080/api

## 預設測試帳號

- **帳號**: admin
- **密碼**: password

## 使用情境

1. 電腦放在櫃檯，員工經過櫃檯時使用電腦畫班表
   - 操作設計簡單迅速
2. 小組長負責排班表，員工可能透過口頭或通訊軟體將班表傳達給小組長
3. 小組長在可排班表的最後一天確定班表，確定後該班表只有管理員可以編輯，但所有人都能查看
   - 擁有管理帳號和密碼的人都是管理員（可能是小組長、大組長、主管等）

## 主要功能

### 1. 班表頁面
- **單一頁面設計**：所有功能集中在一個頁面
- **Navbar 功能**：
  - 登入/登出按鈕（管理者使用）
  - 確認該月班表（管理者）
  - 匯出班表為 Excel（管理者）
  - 新增新員工（管理者）
  - 新增新部門（管理者）

### 2. 員工選擇與班表操作
- **員工列表**：
  - 在 Navbar 下方左側顯示各部門及員工名稱
  - 每個員工有不同的代表色
  - 選擇員工後，該員工的列會呈現代表色（半透明）
  - 每3分鐘自動清除員工選擇
  - 未選擇員工時點選班表會彈出提示：「請先選擇員工」

- **班表顯示**：
  - 未選取員工時不顯示班表
  - 選取員工後，班表出現在員工列表右側
  - 類似 Google Sheet 的表格設計，畫面置中

### 3. 班表表格結構
- **固定欄位**：年月日、星期
- **橫軸**：員工名稱（按部門分組）
- **縱軸**：日期和星期
- **表格範例**（以114年11月為例）：
  ```
  114.11    1    2    3   ...
            六   日   一  ...
  部門A
  員工A     □    ■    □   ...
  員工B     ■    ■    □   ...
  ```

### 4. 顏色標記
- **橘色背景**：店休日（星期日）
- **紅色背景**：員工休假日
- **白色背景**：正常上班日
- **員工代表色**：選中員工時，該列顯示半透明代表色

### 5. 管理員功能
- **登入系統**：
  - 點選登入後彈出 popup 輸入帳號密碼
  - 登入成功後頁面刷新，顯示管理功能按鈕

- **確認班表**：
  - 確認後非管理者無法編輯，管理者仍可異動
  - 點選後彈出確認對話框

- **匯出班表**：
  - 匯出為 Microsoft Excel 格式
  - 文字和格子背景顏色與網頁一致

### 6. 操作日誌
系統記錄所有操作，包含：
- 在員工列表點選員工
- 畫班表（點擊表格塗滿紅色背景）
- 管理者登入
- 管理者確認該月班表
- 管理者匯出班表
- 管理者登出

## 資料庫連線

使用 TablePlus 或其他資料庫工具連線：

- **Host**: localhost
- **Port**: 33060
- **Username**: root
- **Password**: root_password
- **Database**: schedule_db

## 開發說明

### 後端開發

```bash
# 進入後端目錄
cd backend

# 建立新的 migration
php artisan make:migration create_xxx_table

# 建立新的 Model
php artisan make:model ModelName

# 建立新的 Controller
php artisan make:controller ControllerName
```

### 前端開發

```bash
# 進入前端目錄
cd frontend

# 安裝依賴
npm install

# 啟動開發服務器
npm run dev

# 建置生產版本
npm run build
```

## API 文件

### 認證相關
- `POST /api/auth/login` - 管理員登入
- `POST /api/auth/logout` - 管理員登出
- `GET /api/auth/check` - 檢查登入狀態

### 員工相關
- `GET /api/employees` - 取得所有員工列表
- `POST /api/employees/{employee}/select` - 記錄員工被選擇

### 部門相關
- `GET /api/departments` - 取得所有部門列表
- `POST /api/departments` - 新增部門（管理員）
- `POST /api/employees` - 新增員工（管理員）

### 班表相關
- `GET /api/schedules/{year}/{month}` - 取得指定年月的班表
- `POST /api/schedules/records` - 更新班表記錄（切換休假狀態）
- `POST /api/schedules/{schedule}/confirm` - 確認班表（管理員）
- `GET /api/schedules/{year}/{month}/export` - 匯出班表為 Excel（管理員）

### 操作日誌
- `GET /api/activity-logs` - 取得操作日誌（管理員）

## 授權

Private Project
