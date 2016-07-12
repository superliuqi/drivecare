#SIM卡流量充值API文档 v1.0

标签（空格分隔）： API SIM卡


---


# 1. 概述
本文档面向以 API 方式接入 语镜道客系统的合作伙伴，通过API接口对合作伙伴自己名下的微密卡进行查询、充值等操作。

# 2. 接入流程
    a．合作方通过 HTTPS POST 发送请求给语镜服务器。
    b．语镜服务器 返回结果数据；
    c．合作方需要存储返回的交易码用于对账。
    d. 当授权流量池达到警戒值时，会提醒续费。

# 3. 接口说明

## 3.1 流量充值

 - **接口地址：** https://sim.daoke.me/recharge
 - **支持格式：** Json
 - **请求方式：** https POST
 - **请求示例：**

```
POST /recharge HTTPS/1.1
Host:172.16.11.3
Content-Length:96
Content-Type:application/x-www-form-urlencoded

appKey=2064302565&sign=D6B2873C35413896711D7F74CFEEFA51EA52598A&time=14338378467&numid=460011674718225&idtype=2&increment=120

```

 - **接口备注：** 根据用户的sim卡唯一标识，往用户卡内进行充值，并且从授权流量池中扣除对应的流量。
 - **调用样例及调试工具：**
 - **请求参数说明：**
>| 参数名     | 类型 | 是否必传   | 说明 |
| :------- | ----: | ---: |:--- |
|appKey           |  string     | 是        | 应用标识       长度不大于10|
|sign [^footnote]            |  string   | 是         | 安全签名     长度为40|
|numid         |  string     | 是         | 充值ｓｉｍ卡标识符|
|idtype   |  number       | 是         |标识符类型　　１、ｉｍｓｉ　　２、ｉｃｃｉｄ　　３、ｎｕｍｂｅｒ|
|increment   |  number| 是         |只能是100-2000之间的整数  单位：Ｍ|
|time        |  number      | 是         | 请求时间的时间戳 (精确到毫秒) |


 - **返回参数说明**

 JSON返回示例：

	> **失败示例**
	```
	{
        "ERRORCODE":"1",
        "RESULT":"传入参数错误"
    }
	```
	> **成功示例**
	```
	{
	    "ERRORCODE":"0",
	    "RESULT":{
	            "margin":5998393",   //授权流量池剩余流量
	            "threshold":"500",  //充值流量数额
	            "unit":"M",   //流量计算单位
	            "transid":"9998373273934",  //充值交易id  唯一id
	            "numid":"2313123321232131223", // 充值ｓｉｍ卡标识符
	            "idtype":"1"  //标识符类型　　１、ｉｍｓｉ　　２、ｉｃｃｉｄ　　３、ｎｕｍｂｅｒ
        	}
	    }
	}

	```


## 3.2 用户流量查询

 - **接口地址：** https://sim.daoke.me/queryFlow
 - **支持格式：** Json
 - **请求方式：** https POST
 - **请求示例：**

```
POST /queryFlow HTTPS/1.1
Host:172.16.11.3
Content-Length:96
Content-Type:application/x-www-form-urlencoded

appKey=2064302565&sign=417801DE99ABAED7335CB0F6D50567DEBCC0BE3E&time=14338378467&numid=460011674718225&idtype=2

```

 - **接口备注：** 根据用户的sim卡唯一标识查看用户剩余的流量
 - **调用样例及调试工具：**
 - **请求参数说明：**
>| 参数名     | 类型 | 是否必传   | 说明 |
| :------- | ----: | ---: |:--- |
|appKey           |  string     | 是        | 应用标识       长度不大于10|
|sign [^footnote]            |  string   | 是         | 安全签名     长度为40|
|numid         |  string     | 是         | 充值ｓｉｍ卡标识符|
|idtype   |  number       | 是         |标识符类型　　１、ｉｍｓｉ　　２、ｉｃｃｉｄ　　３、ｎｕｍｂｅｒ|
|time        |  number      | 是         | 请求时间的时间戳 (精确到毫秒) |


 - **返回参数说明**

 JSON返回示例：

	> **失败示例**
	```
	{
        "ERRORCODE":"1",
        "RESULT":"传入参数错误"
    }
	```
	> **成功示例**
	```
	{
	    "ERRORCODE":"0",
	    "RESULT":{
	            "margin":599.83",   //剩余流量
	            "total":1000.00",   //本月总流量
	            "unit":"M",   //流量计算单位
	            "numid":"2313123321232131223", // 充值ｓｉｍ卡标识符
	            "idtype":"1"  //标识符类型　　１、ｉｍｓｉ　　２、ｉｃｃｉｄ　　３、ｎｕｍｂｅｒ
        	}
	    }
	}

	```


## 3.3 流量池余量查询

 - **接口地址：** https://sim.daoke.me/queryFlowPool
 - **支持格式：** Json
 - **请求方式：** https POST
 - **请求示例：**

```
POST /queryFlowPool HTTPS/1.1
Host:172.16.11.3
Content-Length:96
Content-Type:application/x-www-form-urlencoded

appKey=2064302565&sign=182D483346B555435436E332D1E2ECC91A0D67A9&time=14338378467
```

 - **接口备注：** 根据用户的sim卡唯一标识查看用户剩余的流量
 - **调用样例及调试工具：**
 - **请求参数说明：**
>| 参数名     | 类型 | 是否必传   | 说明 |
| :------- | ----: | ---: |:--- |
|appKey           |  string     | 是        | 应用标识       长度不大于10|
|sign [^footnote]            |  string   | 是         | 安全签名     长度为40|
|time        |  number      | 是         | 请求时间的时间戳 (精确到毫秒) |


 - **返回参数说明**

 JSON返回示例：

	> **失败示例**
	```
	{
        "ERRORCODE":"1",


        "RESULT":"传入参数错误"
    }
	```
	> **成功示例**
	```
	{
	    "ERRORCODE":"0",
	    "RESULT":{
	        "margin":599.83"
        }

	}

	```


## 3.4 流量回收接口
    对于没有使用完的流量，可以通过该接口将用户账户中的流量回收到流量池，回收的流量的50%会进入授信流量池，可用于再次销售。回收的流量不能大于历史已充值的流量总和。

 - **接口地址：** https://sim.daoke.me/queryFlowPool
 - **支持格式：** Json
 - **请求方式：** https POST
 - **请求示例：**

```
POST /queryFlowPool HTTPS/1.1
Host:172.16.11.3
Content-Length:96
Content-Type:application/x-www-form-urlencoded

appKey=2064302565&sign=182D483346B555435436E332D1E2ECC91A0D67A9&time=14338378467
```

 - **接口备注：** 根据用户的sim卡唯一标识查看用户剩余的流量
 - **调用样例及调试工具：**
 - **请求参数说明：**
>| 参数名     | 类型 | 是否必传   | 说明 |
| :------- | ----: | ---: |:--- |
|appKey           |  string     | 是        | 应用标识       长度不大于10|
|sign [^footnote]            |  string   | 是         | 安全签名     长度为40|
|time        |  number      | 是         | 请求时间的时间戳 (精确到毫秒) |


 - **返回参数说明**

 JSON返回示例：

	> **失败示例**
	```
	{
        "ERRORCODE":"1",
        "RESULT":"传入参数错误"
    }
	```
	> **成功示例**
	```
	{
	    "ERRORCODE":"0",
	    "RESULT":{
	        "margin":599.83"
        }

	}

	```

## 4 流量查询、充值和实名认证页面

### 4.1 流量查询页面
    提供流量查询的H5网页链接，用户可通过页面进行流量查询。

- **接口地址：** https://sim.daoke.me/h5/queryFlow
- **请求方式：** https GET
- **请求参数：**  

>| 参数名     | 类型 | 是否必传   | 说明 |
| :------- | ----: | ---: |:--- |
|appKey           |  string     | 是        | 应用标识       长度不大于10|
|sign [^footnote]            |  string   | 是         | 安全签名     长度为40|
|numid         |  string     | 是         | 充值ｓｉｍ卡标识符|
|model      |string         | 是            |设备识别号
|idtype   |  number       | 是         |标识符类型　　１、ｉｍｓｉ　　２、ｉｃｃｉｄ　　３、ｎｕｍｂｅｒ|
|time        |  number      | 是         | 请求时间的时间戳 (精确到毫秒) |

- **返回参数说明**

### 4.2 流量充值页面
    提供流量充值的H5网页链接，用户可通过网页支付宝扫码，进行流量充值。

- **接口地址：** https://sim.daoke.me/h5/recharge
- **请求方式：** https GET
- **请求参数：**  

>| 参数名     | 类型 | 是否必传   | 说明 |
| :------- | ----: | ---: |:--- |
|appKey           |  string     | 是        | 应用标识       长度不大于10|
|sign [^footnote]            |  string   | 是         | 安全签名     长度为40|
|numid         |  string     | 是         | 充值ｓｉｍ卡标识符|
|model      |string         | 是            |设备识别号
|idtype   |  number       | 是         |标识符类型　　１、ｉｍｓｉ　　２、ｉｃｃｉｄ　　３、ｎｕｍｂｅｒ|
|time        |  number      | 是         | 请求时间的时间戳 (精确到毫秒) |




## 5 附录

### 5.1 错误码

错误编码    | 错误描述                                  |  解决办法
------------|-------------------------------------------|----------------------
0           | Request OK                                |
ME01020     | mysql failed                              | 请与公司客服联系
ME01022     | 系统内部错误                              | 请与公司客服联系
ME01023     | 参数错误                                  | 请检查输入参数
ME01024	    | http body is null!                        | 请检查输入参数
ME24902		| this imsi not find  ip in database		| 请与公司客服联系
ME01025		| http failed	                        	| 请与公司客服联系
ME24907		| update sim flow threshold failed	    	| 请与公司客服联系
ME24901		| set threshold value failed	        	| 请与公司客服联系

[^footnote]:  sign生成

### 5.2 Sign生成方式
> 将带有 appkey 和 secret 的 map 对象中的 key 按字典排序，排序完成后按照 key 的排序
将 key-value 转换成为 StringBuilder 对象，最后将 StringBuilder 对象转换为 String 字符串，调
用 JDK 自带加密算法，最后将加密后的 sign 转换为大写返回
params 参数:
```
cid= df89349
typ= 1
lng= 121.8436
lat= 31.9583
appkey= 20485430303
secret= HKJKSHGKD33434JHUHJHK324GC89
```

Java代码 具体方法如下:
```
public synchronized static String getSign(Map<String, String> map) {
		StringBuffer sbf = new StringBuffer();// 保存生成签名的原生字符串
		String[] arr = map.keySet().toArray(new String[0]);// 获取参数名数组
		Arrays.sort(arr);// 对数组进行排序
		for (String key : arr) {
			if (!key.equals("")) {
				String value = map.get(key);
				if (value == null) {
					value = "";
				}
				sbf.append(key).append(value);
			}
		}
		String sign = null;
		try {
			sign = new String(Hex.encodeHex(DigestUtils.sha(sbf.toString())))
					.toUpperCase();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return sign;
	}
```
