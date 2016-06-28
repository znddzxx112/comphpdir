comphpdir
================================

比较二个存放php文件目录，标出php文件中类,变量,方法的差异


usage
---------------
left_php_dir_path,right_php_dir_path 分别为二个存放php文件目录路径
```
$compphpdir = new Compphpdir('left_php_dir_path','right_php_dir_path');
$compphpdir->proc();
```
output
---------------
file:差异文件名称<br/>
vars:差异变量名<br/>
methods:差异函数名<br/>
```
comp.txt

file:Base_service.php
vars:line
file:Contract_service.php
methods:__construct
methods:create_contract
methods:finance_contract_api
methods:hash_contract_api
methods:create_contract_id
methods:tpl_info_api
file:Finance_service.php
methods:get_config
methods:__construct
methods:index_api
methods:product_api
methods:detail_api
methods:invest_api
file:Order_service.php
methods:get_config
methods:__construct
methods:index_api
methods:page_api
methods:pay_api
methods:cancel_api
methods:contract_api
methods:get_user_order
```