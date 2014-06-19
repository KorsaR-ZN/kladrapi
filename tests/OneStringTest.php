<?php

/**
 * Тесты для поиска одной строкой
 *
 * @author Y. Lichutin
 */
class OneStringTest extends PHPUnit_Framework_TestCase
{  
    /*
     * Тест поиска области
     */
    public function testRegionSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'арханг';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::RegionType);
        $this->assertEquals($res[0]->name, 'Архангельская');
        $this->assertEQuals($res[0]->regionId, 2900000000000);
    }

    /*
     * Тест поиска района
     */
    public function testDistrictSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'краснодарский кр брюховецкий р-н';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::DistrictType);
        $this->assertEquals($res[0]->name, 'Брюховецкий');
        $this->assertEquals($res[0]->regionId, 2300000000000);
        $this->assertEquals($res[0]->districtId, 2300700000000);
    }
    
    /*
     * Тест поиска города
     */
    public function testCitySearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'московская воскресенский белоо';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::CityType);
        $this->assertEquals($res[0]->name, 'Белоозерский');
        $this->assertEquals($res[0]->regionId, 5000000000000);
        $this->assertEquals($res[0]->districtId, 5000400000000); 
        $this->assertEquals($res[0]->cityId, 5000400000800);
    }
  
    /*
     * Тест поиска улицы
     */
    public function testStreetSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'мезенский р каменка ул лесна';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::StreetType);
        $this->assertEquals($res[0]->name, 'Лесная');
        $this->assertEquals($res[0]->regionId, 2900000000000);
        $this->assertEquals($res[0]->districtId, 2901200000000);
        $this->assertEquals($res[0]->cityId, 2901200001800);
        $this->assertEquals($res[0]->streetId, 29012000018000600);
    }
    
    /*
     * Тест поиска здания
     */
    public function testBuildingSearch1()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'петрозаводск ленина д 35а';
        $query->limit = 1;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($res[0]->contentType, QueryToApi::BuildingType);
        $this->assertEquals($res[0]->name, 'д. 35а');
        $this->assertEquals($res[0]->regionId, 1000000000000);
        $this->assertEquals($res[0]->cityId, 1000000100000);
        $this->assertEquals($res[0]->streetId, 10000001000013900); 
        $this->assertEquals($res[0]->buildingId, 1000000100001390004);
    }

    
    /*
     * Проверяет правильность работы лимита
     */
    public function testLimit()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'воронеж пр-т московский 1';
        $query->limit = 10;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($query->limit, count($res));
    }
    
    /*
     * Проверяет, что дома будут искаться по всем улицам запроса, если в первой не найдены
     */
    public function testStreetsChoice()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'воронеж московский 1'; //тут первым находится переулок, в котором домов нет
        $query->limit = 10;
        
        $res = $query->send();
        $res = $res->result;
        
        $this->assertEquals($query->limit, count($res));
    }
    
    /*
     * Проверяет, что дома занимают половину лимита при нормальном запросе
     */
    public function testBuildingLimitNormal()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'москва архангельский 1';
        $query->limit = 5;
        
        $res = $query->send();
        $res = $res->result;
        
        $buildCount = 0;
        
        foreach ($res as $answer)
        {
            if ($answer->contentType == 'building')
            {
                $buildCount += 1;
            }
        }
        
        $this->assertEquals(ceil($query->limit/2), $buildCount);
    }
    
    /*
     * Проверяет, что дома занимают всё свободное место в лимите, если остальных объектов меньше, чем половина лимита
     */
    public function testBuildingLimitLess()
    {
        $query = new QueryToApi();
        $query->oneString = true;
        $query->query = 'ярославль ленина 1';
        $query->limit = 20;
        
        $res = $query->send();
        $res = $res->result;
        
        $buildCount = 0;
        $otherCount = 0;
        foreach ($res as $answer)
        {
            if ($answer->contentType == 'building')
            {
                $buildCount += 1;
            }
            else
            {
                $otherCount += 1;
            }
        }
        
        $this->assertEquals($query->limit, $buildCount+$otherCount);
    }
}
