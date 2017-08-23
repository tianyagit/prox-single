#微擎更新脚本
## 执行更新
暂不支持文件更新 此更新只包含数据库更新,文件需自己覆盖  
执行如下命令  
 
`php console.php upgrade `

会提示更新  输入Y 更新

##创建本地更新文件
>创建本地更新文件只有微擎内部开发人员使用

`php console.php make:upgrade name={name}`

示例  

`php console.php make:upgrade name=update_uniaccount`
