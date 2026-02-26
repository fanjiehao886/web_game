# 财神烧香 - Cocos Creator 版本

这是使用 Cocos Creator 2.4.x 重构的财神烧香游戏，基于原始的 Phaser 版本（caishen1.0.php）。

## 游戏功能

- 加载场景：显示加载进度条和轮播提示文字
- 主游戏场景：
  - 烧香按钮：点击烧香消耗香火
  - 金币雨动画：烧香后播放金币掉落效果
  - 二维码弹窗：关注公众号获取香火
  - 分享弹窗：分享后获得香火奖励
  - 背景音乐：循环播放寺庙背景音乐

## 资源说明

所有游戏资源已复制到 `assets/resources/` 目录：

- 图片资源：`resources/images/caishen/*.png`
- 音频资源：`resources/audio/caishen/*`

## 构建说明

1. 使用 Cocos Creator 2.4.x 打开此项目
2. 在编辑器中配置场景
3. 构建为 Web 平台
4. 将构建后的文件部署到服务器

## 项目结构

```
caishen_cocos/
├── assets/
│   ├── resources/          # 游戏资源
│   │   ├── images/         # 图片资源
│   │   └── audio/          # 音频资源
│   ├── scripts/            # 脚本文件
│   ├── LoadingScene.ts     # 加载场景
│   └── MainScene.ts        # 主游戏场景
├── index.html              # 入口HTML文件
└── README.md               # 说明文档
```

## 开发说明

### LoadingScene
- 负责加载所有游戏资源
- 显示加载进度
- 轮播提示文字（3轮）
- 加载完成后切换到主场景

### MainScene
- 游戏主逻辑
- 烧香功能
- 金币雨动画
- 弹窗管理（二维码、分享）
- 香火计数

## 注意事项

- Cocos Creator 2.4.x 使用 TypeScript
- 资源使用 cc.resources.load 动态加载
- HTML 覆盖层用于显示二维码和分享弹窗
