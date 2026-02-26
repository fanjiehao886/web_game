# Cocos Creator 财神烧香游戏 - 设置指南

## 项目概述

这是一个使用 Cocos Creator 2.4.x 重构的财神烧香游戏，完全基于原始 Phaser 版本（caishen1.0.php）的功能和资源。

## 已完成的工作

### 1. 项目结构
```
caishen_cocos/
├── assets/
│   ├── resources/          # 游戏资源（已复制）
│   │   ├── images/caishen/ # 图片资源 (12个PNG)
│   │   └── audio/caishen/  # 音频资源 (4个音频文件)
│   ├── scripts/            # 脚本文件
│   │   └── generate_meta.js # 元数据生成脚本
│   ├── LoadingScene.ts     # 加载场景脚本
│   ├── LoadingScene.ts.meta
│   ├── LoadingScene.fire   # 加载场景
│   ├── MainScene.ts        # 主游戏场景脚本
│   ├── MainScene.ts.meta
│   └── MainScene.fire      # 主游戏场景
├── index.html              # Web 入口文件
├── build.json              # 构建配置
├── project.json            # 项目配置
└── README.md               # 项目说明
```

### 2. 功能实现

#### LoadingScene（加载场景）
- 显示加载进度条
- 显示加载百分比
- 轮播提示文字（3轮循环）
- 加载所有游戏资源（图片和音频）
- 加载完成后自动切换到主场景

#### MainScene（主游戏场景）
- 游戏UI初始化（背景、香案、香炉、香）
- 香火计数系统
- 烧香按钮交互
- 金币雨动画效果
- 烧香音效和金币掉落音效
- 二维码弹窗（关注公众号）
- 分享弹窗（分享获得香火）
- Toast 提示消息
- 背景音乐循环播放

### 3. 资源复制
所有原始资源已从 `assets/` 目录复制到 `caishen_cocos/assets/resources/`：
- 图片：12个 PNG 文件（背景、香案、香炉、香、按钮等）
- 音频：4个文件（背景音乐、烧香音效、金币掉落音效）

### 4. 元数据生成
已为所有资源文件自动生成 `.meta` 文件，包含唯一的 UUID。

## 使用方法

### 在 Cocos Creator 中打开项目

1. 安装 Cocos Creator 2.4.x 版本
2. 启动 Cocos Creator
3. 选择 "打开其他项目"
4. 导航到 `c:/Users/Administrator/Desktop/Game/caishen_cocos`
5. 点击"打开"

### 配置场景

#### LoadingScene 配置
1. 在编辑器中打开 `LoadingScene.fire`
2. 添加 Canvas 节点
3. 添加 UI 组件：
   - 进度条背景
   - 进度条前景
   - 加载文字标签
4. 将 `LoadingScene.ts` 脚本挂载到 Canvas 节点
5. 绑定组件引用

#### MainScene 配置
1. 在编辑器中打开 `MainScene.fire`
2. 添加 Canvas 节点
3. 添加游戏元素：
   - 背景 Sprite
   - 香案 Sprite
   - 香炉 Sprite
   - 香 Sprite
   - 香火数量 Label
   - 提示文字 Label
   - 烧香按钮 Button
   - Toast Label
   - 弹窗容器
4. 将 `MainScene.ts` 脚本挂载到 Canvas 节点
5. 绑定组件引用
6. 配置按钮点击事件

### 资源加载

在编辑器中：
1. 打开资源管理器
2. 检查 `assets/resources/` 目录
3. 确认所有图片和音频文件已正确导入
4. Cocos Creator 会自动处理 `.meta` 文件

### 构建和运行

1. 点击编辑器菜单 "项目" -> "构建发布"
2. 选择平台：Web Mobile 或 Web Desktop
3. 配置构建参数：
   - 构建路径：`build/`
   - 资源服务器：本地路径或远程服务器
4. 点击"构建"
5. 构建完成后，在浏览器中打开 `build/web-mobile/index.html`

### 本地测试

在 Cocos Creator 中：
1. 点击编辑器顶部的"运行"按钮
2. 选择浏览器预览
3. 游戏会在默认浏览器中启动

## 注意事项

### 资源路径
- 所有资源使用 `cc.resources.load()` 动态加载
- 图片路径：`images/caishen/文件名`
- 音频路径：`audio/caishen/文件名`

### HTML 覆盖层
游戏使用原生 HTML 覆盖层显示二维码和分享弹窗，这些元素会在 `MainScene.ts` 的 `createHtmlElements()` 方法中动态创建。

### 分辨率
游戏设计分辨率：720 x 1280
适配策略：SHOW_ALL（保持比例）

### 音频
- 背景音乐：循环播放
- 音效：单次播放

### 浏览器兼容性
- 支持现代浏览器（Chrome、Firefox、Safari、Edge）
- 需要启用 JavaScript
- 需要支持 HTML5 Audio

## 故障排除

### 资源加载失败
- 检查资源路径是否正确
- 确认 `.meta` 文件存在
- 查看浏览器控制台错误信息

### 场景不切换
- 检查场景名称是否正确
- 确认 `cc.director.loadScene()` 调用成功

### 音频不播放
- 检查浏览器音频策略（需要用户交互）
- 确认音频文件格式正确（mp3、wav）
- 查看控制台错误信息

## 下一步

1. 在 Cocos Creator 编辑器中配置场景和组件
2. 测试游戏功能
3. 根据需要调整 UI 布局
4. 构建并部署到服务器
5. 添加微信分享功能（如果需要）

## 技术支持

如遇到问题，请检查：
- Cocos Creator 文档：https://docs.cocos.com/creator/2.4/
- 浏览器控制台错误信息
- 项目构建日志

---

**项目状态**：代码和资源已准备就绪，需要在 Cocos Creator 编辑器中进行最后的场景配置。
