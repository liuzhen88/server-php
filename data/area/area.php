<?php
defined('emall') or exit('Access Invalid!');
$area_array = 	array (1 => array ( 'area_name'  => '广东省','area_parent_id' =>  '0',),2 => array ( 'area_name'  => '湖北省','area_parent_id' =>  '0',),3 => array ( 'area_name'  => '湖南省','area_parent_id' =>  '0',),4 => array ( 'area_name'  => '澳门特别行政区','area_parent_id' =>  '0',),5 => array ( 'area_name'  => '山东省','area_parent_id' =>  '0',),6 => array ( 'area_name'  => '河南省','area_parent_id' =>  '0',),7 => array ( 'area_name'  => '福建省','area_parent_id' =>  '0',),8 => array ( 'area_name'  => '江西省','area_parent_id' =>  '0',),9 => array ( 'area_name'  => '浙江省','area_parent_id' =>  '0',),10 => array ( 'area_name'  => '安徽省','area_parent_id' =>  '0',),11 => array ( 'area_name'  => '海南省','area_parent_id' =>  '0',),12 => array ( 'area_name'  => '广西壮族自治区','area_parent_id' =>  '0',),13 => array ( 'area_name'  => '四川省','area_parent_id' =>  '0',),14 => array ( 'area_name'  => '贵州省','area_parent_id' =>  '0',),15 => array ( 'area_name'  => '云南省','area_parent_id' =>  '0',),16 => array ( 'area_name'  => '西藏自治区','area_parent_id' =>  '0',),17 => array ( 'area_name'  => '陕西省','area_parent_id' =>  '0',),18 => array ( 'area_name'  => '甘肃省','area_parent_id' =>  '0',),19 => array ( 'area_name'  => '青海省','area_parent_id' =>  '0',),20 => array ( 'area_name'  => '宁夏回族自治区','area_parent_id' =>  '0',),21 => array ( 'area_name'  => '重庆市','area_parent_id' =>  '0',),22 => array ( 'area_name'  => '上海市','area_parent_id' =>  '0',),23 => array ( 'area_name'  => '江苏省','area_parent_id' =>  '0',),24 => array ( 'area_name'  => '天津市','area_parent_id' =>  '0',),25 => array ( 'area_name'  => '北京市','area_parent_id' =>  '0',),26 => array ( 'area_name'  => '辽宁省','area_parent_id' =>  '0',),27 => array ( 'area_name'  => '新疆维吾尔自治区','area_parent_id' =>  '0',),28 => array ( 'area_name'  => '内蒙古自治区','area_parent_id' =>  '0',),29 => array ( 'area_name'  => '山西省','area_parent_id' =>  '0',),30 => array ( 'area_name'  => '香港特别行政区','area_parent_id' =>  '0',),31 => array ( 'area_name'  => '河北省','area_parent_id' =>  '0',),32 => array ( 'area_name'  => '台湾省','area_parent_id' =>  '0',),33 => array ( 'area_name'  => '黑龙江省','area_parent_id' =>  '0',),34 => array ( 'area_name'  => '吉林省','area_parent_id' =>  '0',),35 => array ( 'area_name'  => '揭阳市','area_parent_id' =>  '1',),36 => array ( 'area_name'  => '中山市','area_parent_id' =>  '1',),37 => array ( 'area_name'  => '潮州市','area_parent_id' =>  '1',),38 => array ( 'area_name'  => '清远市','area_parent_id' =>  '1',),39 => array ( 'area_name'  => '东莞市','area_parent_id' =>  '1',),40 => array ( 'area_name'  => '河源市','area_parent_id' =>  '1',),41 => array ( 'area_name'  => '阳江市','area_parent_id' =>  '1',),42 => array ( 'area_name'  => '梅州市','area_parent_id' =>  '1',),43 => array ( 'area_name'  => '汕尾市','area_parent_id' =>  '1',),44 => array ( 'area_name'  => '珠海市','area_parent_id' =>  '1',),45 => array ( 'area_name'  => '云浮市','area_parent_id' =>  '1',),46 => array ( 'area_name'  => '深圳市','area_parent_id' =>  '1',),47 => array ( 'area_name'  => '韶关市','area_parent_id' =>  '1',),48 => array ( 'area_name'  => '惠州市','area_parent_id' =>  '1',),49 => array ( 'area_name'  => '广州市','area_parent_id' =>  '1',),50 => array ( 'area_name'  => '湛江市','area_parent_id' =>  '1',),51 => array ( 'area_name'  => '江门市','area_parent_id' =>  '1',),52 => array ( 'area_name'  => '佛山市','area_parent_id' =>  '1',),53 => array ( 'area_name'  => '汕头市','area_parent_id' =>  '1',),54 => array ( 'area_name'  => '肇庆市','area_parent_id' =>  '1',),55 => array ( 'area_name'  => '茂名市','area_parent_id' =>  '1',),56 => array ( 'area_name'  => '天门市','area_parent_id' =>  '2',),57 => array ( 'area_name'  => '神农架林区','area_parent_id' =>  '2',),58 => array ( 'area_name'  => '仙桃市','area_parent_id' =>  '2',),59 => array ( 'area_name'  => '潜江市','area_parent_id' =>  '2',),60 => array ( 'area_name'  => '随州市','area_parent_id' =>  '2',),61 => array ( 'area_name'  => '恩施土家族苗族自治州','area_parent_id' =>  '2',),62 => array ( 'area_name'  => '宜昌市','area_parent_id' =>  '2',),63 => array ( 'area_name'  => '十堰市','area_parent_id' =>  '2',),64 => array ( 'area_name'  => '黄石市','area_parent_id' =>  '2',),65 => array ( 'area_name'  => '咸宁市','area_parent_id' =>  '2',),66 => array ( 'area_name'  => '武汉市','area_parent_id' =>  '2',),67 => array ( 'area_name'  => '孝感市','area_parent_id' =>  '2',),68 => array ( 'area_name'  => '荆门市','area_parent_id' =>  '2',),69 => array ( 'area_name'  => '鄂州市','area_parent_id' =>  '2',),70 => array ( 'area_name'  => '襄阳市','area_parent_id' =>  '2',),71 => array ( 'area_name'  => '黄冈市','area_parent_id' =>  '2',),72 => array ( 'area_name'  => '荆州市','area_parent_id' =>  '2',),73 => array ( 'area_name'  => '衡阳市','area_parent_id' =>  '3',),74 => array ( 'area_name'  => '湘潭市','area_parent_id' =>  '3',),75 => array ( 'area_name'  => '永州市','area_parent_id' =>  '3',),76 => array ( 'area_name'  => '株洲市','area_parent_id' =>  '3',),77 => array ( 'area_name'  => '长沙市','area_parent_id' =>  '3',),78 => array ( 'area_name'  => '张家界市','area_parent_id' =>  '3',),79 => array ( 'area_name'  => '常德市','area_parent_id' =>  '3',),80 => array ( 'area_name'  => '岳阳市','area_parent_id' =>  '3',),81 => array ( 'area_name'  => '邵阳市','area_parent_id' =>  '3',),82 => array ( 'area_name'  => '郴州市','area_parent_id' =>  '3',),83 => array ( 'area_name'  => '益阳市','area_parent_id' =>  '3',),84 => array ( 'area_name'  => '湘西土家族苗族自治州','area_parent_id' =>  '3',),85 => array ( 'area_name'  => '怀化市','area_parent_id' =>  '3',),86 => array ( 'area_name'  => '娄底市','area_parent_id' =>  '3',),87 => array ( 'area_name'  => '澳门特别行政区','area_parent_id' =>  '4',),88 => array ( 'area_name'  => '滨州市','area_parent_id' =>  '5',),89 => array ( 'area_name'  => '菏泽市','area_parent_id' =>  '5',),90 => array ( 'area_name'  => '德州市','area_parent_id' =>  '5',),91 => array ( 'area_name'  => '聊城市','area_parent_id' =>  '5',),92 => array ( 'area_name'  => '莱芜市','area_parent_id' =>  '5',),93 => array ( 'area_name'  => '临沂市','area_parent_id' =>  '5',),94 => array ( 'area_name'  => '枣庄市','area_parent_id' =>  '5',),95 => array ( 'area_name'  => '淄博市','area_parent_id' =>  '5',),96 => array ( 'area_name'  => '青岛市','area_parent_id' =>  '5',),97 => array ( 'area_name'  => '日照市','area_parent_id' =>  '5',),98 => array ( 'area_name'  => '济南市','area_parent_id' =>  '5',),99 => array ( 'area_name'  => '济宁市','area_parent_id' =>  '5',),100 => array ( 'area_name'  => '潍坊市','area_parent_id' =>  '5',),101 => array ( 'area_name'  => '烟台市','area_parent_id' =>  '5',),102 => array ( 'area_name'  => '东营市','area_parent_id' =>  '5',),103 => array ( 'area_name'  => '威海市','area_parent_id' =>  '5',),104 => array ( 'area_name'  => '泰安市','area_parent_id' =>  '5',),105 => array ( 'area_name'  => '滑县','area_parent_id' =>  '6',),106 => array ( 'area_name'  => '济源市','area_parent_id' =>  '6',),107 => array ( 'area_name'  => '汝州市','area_parent_id' =>  '6',),108 => array ( 'area_name'  => '周口市','area_parent_id' =>  '6',),109 => array ( 'area_name'  => '驻马店市','area_parent_id' =>  '6',),110 => array ( 'area_name'  => '商丘市','area_parent_id' =>  '6',),111 => array ( 'area_name'  => '信阳市','area_parent_id' =>  '6',),112 => array ( 'area_name'  => '三门峡市','area_parent_id' =>  '6',),113 => array ( 'area_name'  => '南阳市','area_parent_id' =>  '6',),114 => array ( 'area_name'  => '平顶山市','area_parent_id' =>  '6',),115 => array ( 'area_name'  => '永城市','area_parent_id' =>  '6',),116 => array ( 'area_name'  => '洛阳市','area_parent_id' =>  '6',),117 => array ( 'area_name'  => '开封市','area_parent_id' =>  '6',),118 => array ( 'area_name'  => '漯河市','area_parent_id' =>  '6',),119 => array ( 'area_name'  => '郑州市','area_parent_id' =>  '6',),120 => array ( 'area_name'  => '焦作市','area_parent_id' =>  '6',),121 => array ( 'area_name'  => '新乡市','area_parent_id' =>  '6',),122 => array ( 'area_name'  => '鹤壁市','area_parent_id' =>  '6',),123 => array ( 'area_name'  => '安阳市','area_parent_id' =>  '6',),124 => array ( 'area_name'  => '许昌市','area_parent_id' =>  '6',),125 => array ( 'area_name'  => '濮阳市','area_parent_id' =>  '6',),126 => array ( 'area_name'  => '三明市','area_parent_id' =>  '7',),127 => array ( 'area_name'  => '莆田市','area_parent_id' =>  '7',),128 => array ( 'area_name'  => '厦门市','area_parent_id' =>  '7',),129 => array ( 'area_name'  => '福州市','area_parent_id' =>  '7',),130 => array ( 'area_name'  => '龙岩市','area_parent_id' =>  '7',),131 => array ( 'area_name'  => '南平市','area_parent_id' =>  '7',),132 => array ( 'area_name'  => '漳州市','area_parent_id' =>  '7',),133 => array ( 'area_name'  => '泉州市','area_parent_id' =>  '7',),134 => array ( 'area_name'  => '宁德市','area_parent_id' =>  '7',),135 => array ( 'area_name'  => '九江市','area_parent_id' =>  '8',),136 => array ( 'area_name'  => '萍乡市','area_parent_id' =>  '8',),137 => array ( 'area_name'  => '上饶市','area_parent_id' =>  '8',),138 => array ( 'area_name'  => '景德镇市','area_parent_id' =>  '8',),139 => array ( 'area_name'  => '南昌市','area_parent_id' =>  '8',),140 => array ( 'area_name'  => '吉安市','area_parent_id' =>  '8',),141 => array ( 'area_name'  => '赣州市','area_parent_id' =>  '8',),142 => array ( 'area_name'  => '鹰潭市','area_parent_id' =>  '8',),143 => array ( 'area_name'  => '新余市','area_parent_id' =>  '8',),144 => array ( 'area_name'  => '抚州市','area_parent_id' =>  '8',),145 => array ( 'area_name'  => '宜春市','area_parent_id' =>  '8',),146 => array ( 'area_name'  => '嘉兴市','area_parent_id' =>  '9',),147 => array ( 'area_name'  => '温州市','area_parent_id' =>  '9',),148 => array ( 'area_name'  => '丽水市','area_parent_id' =>  '9',),149 => array ( 'area_name'  => '宁波市','area_parent_id' =>  '9',),150 => array ( 'area_name'  => '杭州市','area_parent_id' =>  '9',),151 => array ( 'area_name'  => '衢州市','area_parent_id' =>  '9',),152 => array ( 'area_name'  => '金华市','area_parent_id' =>  '9',),153 => array ( 'area_name'  => '绍兴市','area_parent_id' =>  '9',),154 => array ( 'area_name'  => '湖州市','area_parent_id' =>  '9',),155 => array ( 'area_name'  => '台州市','area_parent_id' =>  '9',),156 => array ( 'area_name'  => '舟山市','area_parent_id' =>  '9',),157 => array ( 'area_name'  => '池州市','area_parent_id' =>  '10',),158 => array ( 'area_name'  => '六安市','area_parent_id' =>  '10',),159 => array ( 'area_name'  => '亳州市','area_parent_id' =>  '10',),160 => array ( 'area_name'  => '宿州市','area_parent_id' =>  '10',),161 => array ( 'area_name'  => '宣城市','area_parent_id' =>  '10',),162 => array ( 'area_name'  => '淮南市','area_parent_id' =>  '10',),163 => array ( 'area_name'  => '蚌埠市','area_parent_id' =>  '10',),164 => array ( 'area_name'  => '芜湖市','area_parent_id' =>  '10',),165 => array ( 'area_name'  => '阜阳市','area_parent_id' =>  '10',),166 => array ( 'area_name'  => '合肥市','area_parent_id' =>  '10',),167 => array ( 'area_name'  => '安庆市','area_parent_id' =>  '10',),168 => array ( 'area_name'  => '铜陵市','area_parent_id' =>  '10',),169 => array ( 'area_name'  => '淮北市','area_parent_id' =>  '10',),170 => array ( 'area_name'  => '马鞍山市','area_parent_id' =>  '10',),171 => array ( 'area_name'  => '滁州市','area_parent_id' =>  '10',),172 => array ( 'area_name'  => '黄山市','area_parent_id' =>  '10',),173 => array ( 'area_name'  => '中沙群岛的岛礁及其海域','area_parent_id' =>  '11',),174 => array ( 'area_name'  => '西沙群岛','area_parent_id' =>  '11',),175 => array ( 'area_name'  => '南沙群岛','area_parent_id' =>  '11',),176 => array ( 'area_name'  => '保亭黎族苗族自治县','area_parent_id' =>  '11',),177 => array ( 'area_name'  => '琼中黎族苗族自治县','area_parent_id' =>  '11',),178 => array ( 'area_name'  => '乐东黎族自治县','area_parent_id' =>  '11',),179 => array ( 'area_name'  => '陵水黎族自治县','area_parent_id' =>  '11',),180 => array ( 'area_name'  => '白沙黎族自治县','area_parent_id' =>  '11',),181 => array ( 'area_name'  => '昌江黎族自治县','area_parent_id' =>  '11',),182 => array ( 'area_name'  => '儋州市','area_parent_id' =>  '11',),183 => array ( 'area_name'  => '琼海市','area_parent_id' =>  '11',),184 => array ( 'area_name'  => '五指山市','area_parent_id' =>  '11',),185 => array ( 'area_name'  => '三亚市','area_parent_id' =>  '11',),186 => array ( 'area_name'  => '临高县','area_parent_id' =>  '11',),187 => array ( 'area_name'  => '海口市','area_parent_id' =>  '11',),188 => array ( 'area_name'  => '定安县','area_parent_id' =>  '11',),189 => array ( 'area_name'  => '东方市','area_parent_id' =>  '11',),190 => array ( 'area_name'  => '万宁市','area_parent_id' =>  '11',),191 => array ( 'area_name'  => '文昌市','area_parent_id' =>  '11',),192 => array ( 'area_name'  => '澄迈县','area_parent_id' =>  '11',),193 => array ( 'area_name'  => '屯昌县','area_parent_id' =>  '11',),194 => array ( 'area_name'  => '梧州市','area_parent_id' =>  '12',),195 => array ( 'area_name'  => '桂林市','area_parent_id' =>  '12',),196 => array ( 'area_name'  => '贺州市','area_parent_id' =>  '12',),197 => array ( 'area_name'  => '柳州市','area_parent_id' =>  '12',),198 => array ( 'area_name'  => '南宁市','area_parent_id' =>  '12',),199 => array ( 'area_name'  => '贵港市','area_parent_id' =>  '12',),200 => array ( 'area_name'  => '钦州市','area_parent_id' =>  '12',),201 => array ( 'area_name'  => '防城港市','area_parent_id' =>  '12',),202 => array ( 'area_name'  => '北海市','area_parent_id' =>  '12',),203 => array ( 'area_name'  => '百色市','area_parent_id' =>  '12',),204 => array ( 'area_name'  => '玉林市','area_parent_id' =>  '12',),205 => array ( 'area_name'  => '崇左市','area_parent_id' =>  '12',),206 => array ( 'area_name'  => '河池市','area_parent_id' =>  '12',),207 => array ( 'area_name'  => '来宾市','area_parent_id' =>  '12',),208 => array ( 'area_name'  => '甘孜藏族自治州','area_parent_id' =>  '13',),209 => array ( 'area_name'  => '资阳市','area_parent_id' =>  '13',),210 => array ( 'area_name'  => '阿坝藏族羌族自治州','area_parent_id' =>  '13',),211 => array ( 'area_name'  => '雅安市','area_parent_id' =>  '13',),212 => array ( 'area_name'  => '巴中市','area_parent_id' =>  '13',),213 => array ( 'area_name'  => '广安市','area_parent_id' =>  '13',),214 => array ( 'area_name'  => '达州市','area_parent_id' =>  '13',),215 => array ( 'area_name'  => '眉山市','area_parent_id' =>  '13',),216 => array ( 'area_name'  => '宜宾市','area_parent_id' =>  '13',),217 => array ( 'area_name'  => '泸州市','area_parent_id' =>  '13',),218 => array ( 'area_name'  => '凉山彝族自治州','area_parent_id' =>  '13',),219 => array ( 'area_name'  => '攀枝花市','area_parent_id' =>  '13',),220 => array ( 'area_name'  => '自贡市','area_parent_id' =>  '13',),221 => array ( 'area_name'  => '南充市','area_parent_id' =>  '13',),222 => array ( 'area_name'  => '成都市','area_parent_id' =>  '13',),223 => array ( 'area_name'  => '遂宁市','area_parent_id' =>  '13',),224 => array ( 'area_name'  => '广元市','area_parent_id' =>  '13',),225 => array ( 'area_name'  => '绵阳市','area_parent_id' =>  '13',),226 => array ( 'area_name'  => '德阳市','area_parent_id' =>  '13',),227 => array ( 'area_name'  => '乐山市','area_parent_id' =>  '13',),228 => array ( 'area_name'  => '内江市','area_parent_id' =>  '13',),229 => array ( 'area_name'  => '安顺市','area_parent_id' =>  '14',),230 => array ( 'area_name'  => '遵义市','area_parent_id' =>  '14',),231 => array ( 'area_name'  => '六盘水市','area_parent_id' =>  '14',),232 => array ( 'area_name'  => '贵阳市','area_parent_id' =>  '14',),233 => array ( 'area_name'  => '黔东南苗族侗族自治州','area_parent_id' =>  '14',),234 => array ( 'area_name'  => '毕节地区','area_parent_id' =>  '14',),235 => array ( 'area_name'  => '黔西南布依族苗族自治州','area_parent_id' =>  '14',),236 => array ( 'area_name'  => '铜仁市','area_parent_id' =>  '14',),237 => array ( 'area_name'  => '黔南布依族苗族自治州','area_parent_id' =>  '14',),238 => array ( 'area_name'  => '迪庆藏族自治州','area_parent_id' =>  '15',),239 => array ( 'area_name'  => '德宏傣族景颇族自治州','area_parent_id' =>  '15',),240 => array ( 'area_name'  => '怒江傈僳族自治州','area_parent_id' =>  '15',),241 => array ( 'area_name'  => '西双版纳傣族自治州','area_parent_id' =>  '15',),242 => array ( 'area_name'  => '大理白族自治州','area_parent_id' =>  '15',),243 => array ( 'area_name'  => '保山市','area_parent_id' =>  '15',),244 => array ( 'area_name'  => '玉溪市','area_parent_id' =>  '15',),245 => array ( 'area_name'  => '曲靖市','area_parent_id' =>  '15',),246 => array ( 'area_name'  => '文山壮族苗族自治州','area_parent_id' =>  '15',),247 => array ( 'area_name'  => '昆明市','area_parent_id' =>  '15',),248 => array ( 'area_name'  => '临沧市','area_parent_id' =>  '15',),249 => array ( 'area_name'  => '普洱市','area_parent_id' =>  '15',),250 => array ( 'area_name'  => '丽江市','area_parent_id' =>  '15',),251 => array ( 'area_name'  => '昭通市','area_parent_id' =>  '15',),252 => array ( 'area_name'  => '红河哈尼族彝族自治州','area_parent_id' =>  '15',),253 => array ( 'area_name'  => '楚雄彝族自治州','area_parent_id' =>  '15',),254 => array ( 'area_name'  => '日喀则地区','area_parent_id' =>  '16',),255 => array ( 'area_name'  => '山南地区','area_parent_id' =>  '16',),256 => array ( 'area_name'  => '昌都地区','area_parent_id' =>  '16',),257 => array ( 'area_name'  => '拉萨市','area_parent_id' =>  '16',),258 => array ( 'area_name'  => '林芝地区','area_parent_id' =>  '16',),259 => array ( 'area_name'  => '阿里地区','area_parent_id' =>  '16',),260 => array ( 'area_name'  => '那曲地区','area_parent_id' =>  '16',),261 => array ( 'area_name'  => '咸阳市','area_parent_id' =>  '17',),262 => array ( 'area_name'  => '宝鸡市','area_parent_id' =>  '17',),263 => array ( 'area_name'  => '铜川市','area_parent_id' =>  '17',),264 => array ( 'area_name'  => '西安市','area_parent_id' =>  '17',),265 => array ( 'area_name'  => '榆林市','area_parent_id' =>  '17',),266 => array ( 'area_name'  => '汉中市','area_parent_id' =>  '17',),267 => array ( 'area_name'  => '延安市','area_parent_id' =>  '17',),268 => array ( 'area_name'  => '渭南市','area_parent_id' =>  '17',),269 => array ( 'area_name'  => '商洛市','area_parent_id' =>  '17',),270 => array ( 'area_name'  => '安康市','area_parent_id' =>  '17',),271 => array ( 'area_name'  => '白银市','area_parent_id' =>  '18',),272 => array ( 'area_name'  => '金昌市','area_parent_id' =>  '18',),273 => array ( 'area_name'  => '定西市','area_parent_id' =>  '18',),274 => array ( 'area_name'  => '嘉峪关市','area_parent_id' =>  '18',),275 => array ( 'area_name'  => '兰州市','area_parent_id' =>  '18',),276 => array ( 'area_name'  => '平凉市','area_parent_id' =>  '18',),277 => array ( 'area_name'  => '张掖市','area_parent_id' =>  '18',),278 => array ( 'area_name'  => '武威市','area_parent_id' =>  '18',),279 => array ( 'area_name'  => '天水市','area_parent_id' =>  '18',),280 => array ( 'area_name'  => '庆阳市','area_parent_id' =>  '18',),281 => array ( 'area_name'  => '酒泉市','area_parent_id' =>  '18',),282 => array ( 'area_name'  => '甘南藏族自治州','area_parent_id' =>  '18',),283 => array ( 'area_name'  => '陇南市','area_parent_id' =>  '18',),284 => array ( 'area_name'  => '临夏回族自治州','area_parent_id' =>  '18',),285 => array ( 'area_name'  => '黄南藏族自治州','area_parent_id' =>  '19',),286 => array ( 'area_name'  => '海北藏族自治州','area_parent_id' =>  '19',),287 => array ( 'area_name'  => '海东市','area_parent_id' =>  '19',),288 => array ( 'area_name'  => '西宁市','area_parent_id' =>  '19',),289 => array ( 'area_name'  => '海西蒙古族藏族自治州','area_parent_id' =>  '19',),290 => array ( 'area_name'  => '海南藏族自治州','area_parent_id' =>  '19',),291 => array ( 'area_name'  => '玉树藏族自治州','area_parent_id' =>  '19',),292 => array ( 'area_name'  => '果洛藏族自治州','area_parent_id' =>  '19',),293 => array ( 'area_name'  => '固原市','area_parent_id' =>  '20',),294 => array ( 'area_name'  => '吴忠市','area_parent_id' =>  '20',),295 => array ( 'area_name'  => '石嘴山市','area_parent_id' =>  '20',),296 => array ( 'area_name'  => '银川市','area_parent_id' =>  '20',),297 => array ( 'area_name'  => '中卫市','area_parent_id' =>  '20',),298 => array ( 'area_name'  => '重庆市','area_parent_id' =>  '21',),299 => array ( 'area_name'  => '上海市','area_parent_id' =>  '22',),300 => array ( 'area_name'  => '常州市','area_parent_id' =>  '23',),301 => array ( 'area_name'  => '徐州市','area_parent_id' =>  '23',),302 => array ( 'area_name'  => '镇江市','area_parent_id' =>  '23',),303 => array ( 'area_name'  => '无锡市','area_parent_id' =>  '23',),304 => array ( 'area_name'  => '南京市','area_parent_id' =>  '23',),305 => array ( 'area_name'  => '淮安市','area_parent_id' =>  '23',),306 => array ( 'area_name'  => '连云港市','area_parent_id' =>  '23',),307 => array ( 'area_name'  => '南通市','area_parent_id' =>  '23',),308 => array ( 'area_name'  => '苏州市','area_parent_id' =>  '23',),309 => array ( 'area_name'  => '扬州市','area_parent_id' =>  '23',),310 => array ( 'area_name'  => '盐城市','area_parent_id' =>  '23',),311 => array ( 'area_name'  => '泰州市','area_parent_id' =>  '23',),312 => array ( 'area_name'  => '宿迁市','area_parent_id' =>  '23',),313 => array ( 'area_name'  => '天津市','area_parent_id' =>  '24',),314 => array ( 'area_name'  => '北京市','area_parent_id' =>  '25',),315 => array ( 'area_name'  => '抚顺市','area_parent_id' =>  '26',),316 => array ( 'area_name'  => '鞍山市','area_parent_id' =>  '26',),317 => array ( 'area_name'  => '盘锦市','area_parent_id' =>  '26',),318 => array ( 'area_name'  => '大连市','area_parent_id' =>  '26',),319 => array ( 'area_name'  => '沈阳市','area_parent_id' =>  '26',),320 => array ( 'area_name'  => '营口市','area_parent_id' =>  '26',),321 => array ( 'area_name'  => '锦州市','area_parent_id' =>  '26',),322 => array ( 'area_name'  => '丹东市','area_parent_id' =>  '26',),323 => array ( 'area_name'  => '本溪市','area_parent_id' =>  '26',),324 => array ( 'area_name'  => '辽阳市','area_parent_id' =>  '26',),325 => array ( 'area_name'  => '阜新市','area_parent_id' =>  '26',),326 => array ( 'area_name'  => '葫芦岛市','area_parent_id' =>  '26',),327 => array ( 'area_name'  => '铁岭市','area_parent_id' =>  '26',),328 => array ( 'area_name'  => '朝阳市','area_parent_id' =>  '26',),329 => array ( 'area_name'  => '铁门关市','area_parent_id' =>  '27',),330 => array ( 'area_name'  => '五家渠市','area_parent_id' =>  '27',),331 => array ( 'area_name'  => '北屯市','area_parent_id' =>  '27',),332 => array ( 'area_name'  => '阿拉尔市','area_parent_id' =>  '27',),333 => array ( 'area_name'  => '图木舒克市','area_parent_id' =>  '27',),334 => array ( 'area_name'  => '阿勒泰地区','area_parent_id' =>  '27',),335 => array ( 'area_name'  => '石河子市','area_parent_id' =>  '27',),336 => array ( 'area_name'  => '伊犁哈萨克自治州','area_parent_id' =>  '27',),337 => array ( 'area_name'  => '塔城地区','area_parent_id' =>  '27',),338 => array ( 'area_name'  => '哈密地区','area_parent_id' =>  '27',),339 => array ( 'area_name'  => '双河市','area_parent_id' =>  '27',),340 => array ( 'area_name'  => '吐鲁番地区','area_parent_id' =>  '27',),341 => array ( 'area_name'  => '克拉玛依市','area_parent_id' =>  '27',),342 => array ( 'area_name'  => '和田地区','area_parent_id' =>  '27',),343 => array ( 'area_name'  => '乌鲁木齐市','area_parent_id' =>  '27',),344 => array ( 'area_name'  => '阿克苏地区','area_parent_id' =>  '27',),345 => array ( 'area_name'  => '巴音郭楞蒙古自治州','area_parent_id' =>  '27',),346 => array ( 'area_name'  => '博尔塔拉蒙古自治州','area_parent_id' =>  '27',),347 => array ( 'area_name'  => '昌吉回族自治州','area_parent_id' =>  '27',),348 => array ( 'area_name'  => '喀什地区','area_parent_id' =>  '27',),349 => array ( 'area_name'  => '克孜勒苏柯尔克孜自治州','area_parent_id' =>  '27',),350 => array ( 'area_name'  => '赤峰市','area_parent_id' =>  '28',),351 => array ( 'area_name'  => '乌海市','area_parent_id' =>  '28',),352 => array ( 'area_name'  => '锡林郭勒盟','area_parent_id' =>  '28',),353 => array ( 'area_name'  => '包头市','area_parent_id' =>  '28',),354 => array ( 'area_name'  => '呼和浩特市','area_parent_id' =>  '28',),355 => array ( 'area_name'  => '巴彦淖尔市','area_parent_id' =>  '28',),356 => array ( 'area_name'  => '呼伦贝尔市','area_parent_id' =>  '28',),357 => array ( 'area_name'  => '鄂尔多斯市','area_parent_id' =>  '28',),358 => array ( 'area_name'  => '通辽市','area_parent_id' =>  '28',),359 => array ( 'area_name'  => '兴安盟','area_parent_id' =>  '28',),360 => array ( 'area_name'  => '乌兰察布市','area_parent_id' =>  '28',),361 => array ( 'area_name'  => '阿拉善盟','area_parent_id' =>  '28',),362 => array ( 'area_name'  => '长治市','area_parent_id' =>  '29',),363 => array ( 'area_name'  => '阳泉市','area_parent_id' =>  '29',),364 => array ( 'area_name'  => '吕梁市','area_parent_id' =>  '29',),365 => array ( 'area_name'  => '大同市','area_parent_id' =>  '29',),366 => array ( 'area_name'  => '太原市','area_parent_id' =>  '29',),367 => array ( 'area_name'  => '运城市','area_parent_id' =>  '29',),368 => array ( 'area_name'  => '晋中市','area_parent_id' =>  '29',),369 => array ( 'area_name'  => '朔州市','area_parent_id' =>  '29',),370 => array ( 'area_name'  => '晋城市','area_parent_id' =>  '29',),371 => array ( 'area_name'  => '临汾市','area_parent_id' =>  '29',),372 => array ( 'area_name'  => '忻州市','area_parent_id' =>  '29',),373 => array ( 'area_name'  => '香港特别行政区','area_parent_id' =>  '30',),374 => array ( 'area_name'  => '邯郸市','area_parent_id' =>  '31',),375 => array ( 'area_name'  => '秦皇岛市','area_parent_id' =>  '31',),376 => array ( 'area_name'  => '衡水市','area_parent_id' =>  '31',),377 => array ( 'area_name'  => '唐山市','area_parent_id' =>  '31',),378 => array ( 'area_name'  => '石家庄市','area_parent_id' =>  '31',),379 => array ( 'area_name'  => '承德市','area_parent_id' =>  '31',),380 => array ( 'area_name'  => '张家口市','area_parent_id' =>  '31',),381 => array ( 'area_name'  => '保定市','area_parent_id' =>  '31',),382 => array ( 'area_name'  => '邢台市','area_parent_id' =>  '31',),383 => array ( 'area_name'  => '廊坊市','area_parent_id' =>  '31',),384 => array ( 'area_name'  => '沧州市','area_parent_id' =>  '31',),385 => array ( 'area_name'  => '台东县','area_parent_id' =>  '32',),386 => array ( 'area_name'  => '高雄市','area_parent_id' =>  '32',),387 => array ( 'area_name'  => '屏东县','area_parent_id' =>  '32',),388 => array ( 'area_name'  => '嘉义县','area_parent_id' =>  '32',),389 => array ( 'area_name'  => '台南市','area_parent_id' =>  '32',),390 => array ( 'area_name'  => '南投县','area_parent_id' =>  '32',),391 => array ( 'area_name'  => '云林县','area_parent_id' =>  '32',),392 => array ( 'area_name'  => '台中市','area_parent_id' =>  '32',),393 => array ( 'area_name'  => '彰化县','area_parent_id' =>  '32',),394 => array ( 'area_name'  => '新竹市','area_parent_id' =>  '32',),395 => array ( 'area_name'  => '澎湖县','area_parent_id' =>  '32',),396 => array ( 'area_name'  => '花莲县','area_parent_id' =>  '32',),397 => array ( 'area_name'  => '基隆市','area_parent_id' =>  '32',),398 => array ( 'area_name'  => '高雄县','area_parent_id' =>  '32',),399 => array ( 'area_name'  => '苗栗县','area_parent_id' =>  '32',),400 => array ( 'area_name'  => '台北市','area_parent_id' =>  '32',),401 => array ( 'area_name'  => '宜兰县','area_parent_id' =>  '32',),402 => array ( 'area_name'  => '台北县','area_parent_id' =>  '32',),403 => array ( 'area_name'  => '嘉义市','area_parent_id' =>  '32',),404 => array ( 'area_name'  => '台中县','area_parent_id' =>  '32',),405 => array ( 'area_name'  => '新竹县','area_parent_id' =>  '32',),406 => array ( 'area_name'  => '桃园县','area_parent_id' =>  '32',),407 => array ( 'area_name'  => '鹤岗市','area_parent_id' =>  '33',),408 => array ( 'area_name'  => '鸡西市','area_parent_id' =>  '33',),409 => array ( 'area_name'  => '黑河市','area_parent_id' =>  '33',),410 => array ( 'area_name'  => '齐齐哈尔市','area_parent_id' =>  '33',),411 => array ( 'area_name'  => '哈尔滨市','area_parent_id' =>  '33',),412 => array ( 'area_name'  => '佳木斯市','area_parent_id' =>  '33',),413 => array ( 'area_name'  => '伊春市','area_parent_id' =>  '33',),414 => array ( 'area_name'  => '大庆市','area_parent_id' =>  '33',),415 => array ( 'area_name'  => '双鸭山市','area_parent_id' =>  '33',),416 => array ( 'area_name'  => '牡丹江市','area_parent_id' =>  '33',),417 => array ( 'area_name'  => '七台河市','area_parent_id' =>  '33',),418 => array ( 'area_name'  => '绥化市','area_parent_id' =>  '33',),419 => array ( 'area_name'  => '大兴安岭地区','area_parent_id' =>  '33',),420 => array ( 'area_name'  => '辽源市','area_parent_id' =>  '34',),421 => array ( 'area_name'  => '四平市','area_parent_id' =>  '34',),422 => array ( 'area_name'  => '梅河口市','area_parent_id' =>  '34',),423 => array ( 'area_name'  => '吉林市','area_parent_id' =>  '34',),424 => array ( 'area_name'  => '长春市','area_parent_id' =>  '34',),425 => array ( 'area_name'  => '白城市','area_parent_id' =>  '34',),426 => array ( 'area_name'  => '松原市','area_parent_id' =>  '34',),427 => array ( 'area_name'  => '白山市','area_parent_id' =>  '34',),428 => array ( 'area_name'  => '通化市','area_parent_id' =>  '34',),429 => array ( 'area_name'  => '公主岭市','area_parent_id' =>  '34',),430 => array ( 'area_name'  => '延边朝鲜族自治州','area_parent_id' =>  '34',),);