# 手动配置场景指南

由于 `.fire` 场景文件格式复杂，建议在 Cocos Creator 编辑器中手动创建场景。

## 第一步：在 Cocos Creator 中打开项目

1. 启动 Cocos Creator 2.4.x
2. 点击 "打开其他项目"
3. 选择 `c:/Users/Administrator/Desktop/Game/caishen_cocos`
4. 点击"打开"

## 第二步：创建 LoadingScene

1. 在资源管理器中，右键点击 `assets` 文件夹
2. 选择 "创建" -> "Scene"
3. 命名为 `LoadingScene`

4. 在层级管理器中，选择 `Canvas` 节点
5. 在属性检查器中设置 Canvas 设计分辨率：
   - 宽度：720
   - 高度：1280
   - 适配高度：勾选

6. 在 Canvas 下创建以下子节点：

   **loadingLabel 节点**：
   - 右键 Canvas -> 创建节点 -> Label
   - 命名：loadingLabel
   - 位置：(0, 20)
   - Label 组件：
     - String: 加载中...
     - Font Size: 40
     - 颜色：白色

   **progressBar 节点**：
   - 右键 Canvas -> 创建节点 -> 空节点
   - 命名：progressBar
   - 位置：(0, -20)
   - Content Size: (300, 30)

   在 progressBar 下创建 bar 子节点：
   - 右键 progressBar -> 创建节点 -> Sprite
   - 命名：bar
   - 位置：(-150, 0)
   - Content Size: (0, 30)
   - Sprite 组件：
     - Type: SIMPLE
     - Size Mode: CUSTOM

7. 将 `LoadingScene.ts` 脚本拖拽到 Canvas 节点上
8. 保存场景

## 第三步：创建 MainScene

1. 在资源管理器中，右键点击 `assets` 文件夹
2. 选择 "创建" -> "Scene"
3. 命名为 `MainScene`

4. 在层级管理器中，选择 `Canvas` 节点
5. 在属性检查器中设置 Canvas 设计分辨率：
   - 宽度：720
   - 高度：1280
   - 适配高度：勾选

6. 将 `MainScene.ts` 脚本拖拽到 Canvas 节点上
7. 保存场景

**注意**：`MainScene.ts` 中的 `initGame()` 方法会动态创建所有游戏节点，所以不需要在场景编辑器中手动添加节点。

## 第四步：设置项目启动场景

1. 点击菜单 "项目" -> "项目设置"
2. 在 "启动场景" 中选择 `LoadingScene`
3. 点击"保存"

## 第五步：运行测试

1. 点击编辑器顶部的"运行"按钮（或按 F8）
2. 选择浏览器预览
3. 游戏会在浏览器中启动

## 验证资源加载

在资源管理器中检查：
- `assets/resources/images/caishen/` 应该有 12 个图片文件（每个都有 .meta）
- `assets/resources/audio/caishen/` 应该有 4 个音频文件（每个都有 .meta）

如果资源显示为"未加载"或图标缺失：
1. 右键点击资源文件夹
2. 选择 "刷新"
3. 或者关闭并重新打开项目

## 常见问题

### Q: 场景打不开或报错？
A: 删除当前的 `.fire` 文件，在编辑器中重新创建

### Q: 脚本挂载后没有反应？
A:
- 检查脚本中是否有语法错误
- 查看控制台是否有错误信息
- 确认脚本文件名与类名一致

### Q: 资源加载失败？
A:
- 确认资源路径正确
- 检查 `.meta` 文件是否存在
- 查看控制台错误信息

### Q: 场景切换不成功？
A:
- 确认场景名称正确
- 检查 `cc.director.loadScene()` 调用
- 确认场景文件名（不含扩展名）

## 下一步

场景配置完成后：
1. 运行游戏测试
2. 根据需要调整 UI 布局
3. 添加微信分享功能（如果需要）
4. 构建并部署到服务器

## 技术说明

### 为什么手动创建场景更好？
- `.fire` 文件格式复杂，手动编写容易出错
- 编辑器会自动处理节点引用和 UUID
- 可视化编辑更直观
- 避免版本控制冲突

### MainScene 的动态创建方式
`MainScene.ts` 使用代码动态创建所有 UI 节点，这种方式：
- 灵活性高，可以动态调整
- 减少场景文件体积
- 便于程序化控制
- 但失去可视化编辑的优势

如果更喜欢可视化编辑，可以修改 `MainScene.ts`，去掉动态创建代码，改为在场景编辑器中手动创建节点。
