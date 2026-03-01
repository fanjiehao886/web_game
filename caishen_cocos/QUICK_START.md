# 快速开始指南

## 项目状态
✅ 代码和资源已准备完成
✅ 所有 meta 文件已生成
✅ 可以在 Cocos Creator 中打开

## 已完成的工作

### 1. 场景文件
- `LoadingScene.fire` + `LoadingScene.ts` - 加载场景
- `MainScene.fire` + `MainScene.ts` - 主游戏场景

### 2. 游戏资源
- 图片：12个 PNG 文件（背景、香案、香炉、香、按钮等）
- 音频：4个文件（背景音乐、烧香音效、金币掉落音效）
- 所有资源已复制到 `assets/resources/` 目录
- 所有 meta 文件已生成

### 3. 游戏功能
- 加载场景：进度条、百分比、轮播提示文字
- 主游戏场景：
  - 香火计数系统
  - 烧香按钮交互
  - 金币雨动画
  - 音效播放
  - 二维码弹窗
  - 分享弹窗
  - Toast 提示
  - 背景音乐

## 使用步骤

### 第一步：在 Cocos Creator 中打开项目
1. 启动 Cocos Creator 2.4.x
2. 点击 "打开其他项目"
3. 选择 `c:/Users/Administrator/Desktop/Game/caishen_cocos`
4. 点击"打开"

### 第二步：配置 LoadingScene
1. 在资源管理器中找到 `LoadingScene.fire`，双击打开
2. 在层级管理器中会看到 Canvas 节点
3. 将 `LoadingScene.ts` 脚本拖拽到 Canvas 节点上
4. 在属性检查器中绑定组件引用：
   - loadingLabel: 加载文字标签节点
   - progressBar: 进度条节点

### 第三步：配置 MainScene
1. 在资源管理器中找到 `MainScene.fire`，双击打开
2. 在层级管理器中会看到 Canvas 节点和子节点
3. 将 `MainScene.ts` 脚本拖拽到 Canvas 节点上
4. 在属性检查器中不需要手动绑定（脚本中已动态创建节点）

### 第四步：运行测试
1. 点击编辑器顶部的 "运行" 按钮（或按 F8）
2. 选择浏览器预览
3. 游戏会在浏览器中启动

### 第五步：构建发布
1. 点击菜单 "项目" -> "构建发布"
2. 选择平台：Web Mobile
3. 配置构建设置：
   - 构建路径：`build/`
   - MD5 Cache：勾选
4. 点击"构建"
5. 构建完成后，在 `build/web-mobile/` 目录下找到 `index.html`

## 注意事项

### 场景配置提示
- `MainScene.ts` 中的 `initGame()` 方法会动态创建所有游戏UI节点
- 你可以选择使用动态创建的方式，也可以在场景编辑器中手动创建节点
- 如果选择手动创建，需要将节点拖拽到脚本的属性中进行绑定

### 资源加载
- 游戏使用 `cc.resources.load()` 动态加载资源
- 确保资源路径正确：
  - 图片：`images/caishen/文件名`
  - 音频：`audio/caishen/文件名`

### 浏览器测试
- 首次运行时，浏览器可能需要用户交互才能播放音频
- 建议在真实浏览器中测试，而不是在 Cocos Creator 预览中

## 常见问题

**Q: 场景打不开？**
A: 确保 Cocos Creator 版本为 2.4.x

**Q: 资源加载失败？**
A: 检查 `assets/resources/` 目录下是否有对应的图片和音频文件

**Q: 音频不播放？**
A: 浏览器策略要求用户交互后才能播放音频，先点击屏幕任意位置

**Q: 构建失败？**
A: 清理构建缓存：删除 `build/` 和 `library/` 目录，重新构建

## 项目结构
```
caishen_cocos/
├── assets/
│   ├── resources/           # 游戏资源
│   │   ├── images/caishen/  # 图片（12个PNG）
│   │   └── audio/caishen/   # 音频（4个文件）
│   ├── scripts/             # 脚本目录（空）
│   ├── LoadingScene.fire    # 加载场景
│   ├── LoadingScene.ts      # 加载场景脚本
│   ├── MainScene.fire       # 主游戏场景
│   └── MainScene.ts         # 主游戏场景脚本
├── index.html               # Web 入口文件
├── project.json             # 项目配置
├── README.md               # 项目说明
└── QUICK_START.md          # 本文件
```

## 技术支持
- Cocos Creator 文档：https://docs.cocos.com/creator/2.4/
- 查看浏览器控制台获取详细错误信息
