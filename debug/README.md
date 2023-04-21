# 隐式调试工具

格式化打印变量信息，支持打印对象属性和方法。支持记录变量值或打印到页面但不直接显示，可在控制台调用 js 显示出来，利于线上硬调试。

# 用法

1.加载脚本 `include_one 'imdebug/load.php'`

2.调用 `show` 和 `hide` 设置是否直接显示到页面，默认是显示

```php
\Imon\Debug::hide();
```

通过控制台执行 js 显示打印结果

```js
javascript: document.querySelectorAll('.imdebug-box').forEach((el) => {
    el.style.display = 'block'
})
```

3.打印数据

```php
\Imon\Debug::dump(12345);
```

4.记录数据

```php
\Imon\Debug::log([1,2,3]);
```

4.记录打印的 html

```php
\Imon\Debug::logHTML([1,2,3]);
```

6.浏览器打开 imdebug 目录即可查看所有记录过的数据 `http://localhost/imdebug`
