# 场景切换流程说明

## 游戏启动流程

```
启动游戏
    ↓
LoadingScene (启动场景)
    ├─ 显示加载界面
    ├─ 加载资源 (14个)
    │   ├─ 图片: 11个
    │   └─ 音频: 3个
    ├─ 更新进度条
    ├─ 显示提示文字 (3轮)
    └─ 加载完成后
        ↓ (延迟0.5秒)
        MainScene
            ├─ 初始化游戏
            ├─ 动态创建UI
            ├─ 打开二维码弹窗
            └─ 游戏开始
```

## 配置说明

### project.json
```json
{
  "startScene": "3688edb7-eba8-4a5f-bd36-5e3374938e7c"  // LoadingScene 的 UUID
}
```

### LoadingScene.fire
- 脚本挂载节点: Canvas
- 脚本类型: LoadingScene
- 绑定属性:
  - `loadingLabel`: 加载文字标签
  - `barSprite`: 进度条 Sprite

### MainScene.fire
- 脚本挂载节点: MainScene
- 脚本类型: MainScene
- 所有UI动态创建,无需绑定

## LoadingScene 工作流程

### 1. onLoad()
```typescript
onLoad() {
    this.loadResources();  // 开始加载资源
    this.showTips();       // 开始显示提示
}
```

### 2. loadResources()
```typescript
loadResources() {
    const resources = [
        // 11个图片
        { type: 'image', url: 'images/caishen/bg_caishen' },
        { type: 'image', url: 'images/caishen/altar' },
        // ...

        // 3个音频
        { type: 'audio', url: 'audio/caishen/burn_sound' },
        // ...
    ];

    resources.forEach((res) => {
        cc.loader.loadRes(res.url, type, (err, asset) => {
            this.updateProgress(++loadedCount, total);
        });
    });
}
```

### 3. updateProgress()
```typescript
updateProgress(current: number, total: number) {
    this.progress = current / total;

    // 更新进度条宽度
    this.barSprite.node.width = 300 * this.progress;

    // 更新文字
    this.loadingLabel.string = `加载中... ${Math.round(this.progress * 100)}%`;

    // 所有资源加载完成
    if (current >= total) {
        this.scheduleOnce(() => {
            cc.director.loadScene('MainScene');  // 切换场景
        }, 0.5);  // 延迟0.5秒
    }
}
```

### 4. showTips()
```typescript
showTips() {
    // 显示提示文字
    // 淡入 -> 停留 -> 淡出 -> 下一条
    // 共3轮,每轮4条提示
}
```

## MainScene 工作流程

### 1. onLoad()
```typescript
onLoad() {
    this.initGame();    // 初始化游戏
    this.setupEvents(); // 设置事件

    // 延迟打开二维码弹窗
    this.scheduleOnce(() => {
        this.openQrcodePopup();
    }, 1);
}
```

### 2. initGame()
```typescript
initGame() {
    // 动态创建所有UI节点
    this.loadResources();  // 加载背景、香案、香炉等
    this.createUI();       // 创建按钮、标签等
    this.createPopup();    // 创建弹窗
}
```

## 关键点

### 1. 资源加载
- 所有资源路径不带扩展名
- 路径格式: `images/caishen/bg_caishen` (不是 `bg_caishen.png`)
- 资源在 `assets/resources/` 目录下

### 2. 场景切换
- 使用 `cc.director.loadScene('MainScene')`
- 场景名称必须与文件名匹配
- 切换前会自动释放当前场景资源

### 3. 进度条更新
- 通过修改 Node 的 width 属性
- 初始宽度: 0
- 最大宽度: 300
- 公式: `width = 300 * (当前进度 / 总数)`

### 4. 提示文字轮播
- 共3轮,每轮4条提示
- 使用 Action 动画系统
- 淡入 -> 停留0.2秒 -> 淡出 -> 下一条

## 调试技巧

### 查看加载进度
打开浏览器控制台,会看到:
```
加载成功: images/caishen/bg_caishen
加载成功: images/caishen/altar
...
所有资源加载完成，准备切换到 MainScene
切换到 MainScene
MainScene onLoad
```

### 如果场景不切换
1. 检查控制台是否有错误
2. 确认资源是否全部加载成功
3. 确认 `cc.director.loadScene()` 是否被调用
4. 确认 MainScene.fire 是否存在且格式正确

### 如果资源加载失败
1. 检查资源路径是否正确(不带扩展名)
2. 检查资源文件是否在 `assets/resources/` 目录
3. 检查 .meta 文件是否存在
4. 查看控制台错误信息

## 场景文件

### LoadingScene.fire
```
Canvas (脚本: LoadingScene)
├── bg (背景节点)
├── loadingLabel (加载文字)
└── progressBar
    └── bar (进度条,Sprite组件)
```

### MainScene.fire
```
Canvas
└── MainScene (脚本: MainScene)
    (所有UI动态创建)
```

## 时间线

```
0.0s  - LoadingScene 加载
0.0s  - 开始加载资源
0.0s  - 开始显示提示
~1.5s - 所有资源加载完成(14个)
2.0s  - 切换到 MainScene
2.0s  - MainScene onLoad
3.0s  - 打开二维码弹窗
```

## 常见问题

### Q: 为什么进度条不显示?
A: 检查 barSprite 属性是否正确绑定到 progressBar/bar 节点的 Sprite 组件

### Q: 为什么场景不切换?
A:
1. 检查所有资源是否加载成功
2. 检查 current >= total 条件是否满足
3. 查看控制台错误信息

### Q: 提示文字不显示?
A: 提示文字是动态创建的,会自动显示在 Canvas 上方

### Q: 如何跳过加载场景?
A: 修改 project.json 的 startScene 为 MainScene 的 UUID

---

**最后更新**: 2026-03-02
**版本**: 1.0
