<?php

namespace Openregion\Ispringintegration;

class CStructureHelper
{
	const IBLOCK_CODE = 'enrollments';

	public static function getUsers()
	{
		$filter =
			[
				'ACTIVE' => 'Y',
				'UF_ISPRING'  => true,
			];
		$arResponse = \CUser::GetList('ID', 'asc', $filter);

		while ($arUser = $arResponse->Fetch()) {
			if (!empty($arUser['EMAIL'])) {
				$arResult[] =
					[
						'userId' => $arUser['ID'],
						'login' => $arUser['LOGIN'],
						'first_name' => $arUser['NAME'],
						'last_name' => $arUser['LAST_NAME'],
						'email' => $arUser['EMAIL'],
						'job_title' => $arUser['WORK_POSITION'],
						'departmentId' => $arUser['UF_DEPARTMENT']['0']
					];
			}
		}

		return $arResult;
	}


	public static function getDepartment($iBlockId)
	{

		$arFilter =
			[
				'IBLOCK_ID' => $iBlockId,
				'GLOBAL_ACTIVE' => 'Y',
			];

		$arResponse = \CIBlockSection::GetList(
			['IBLOCK_SECTION_ID' => 'asc'],
			$arFilter,
			false,
			['UF_HEAD']
		);


		while ($department = $arResponse->Fetch()) {

			$arDepartment[$department['ID']] =
				[
					'code' => $department['ID'],
					'name' => trim($department['NAME'], " \t"),
					'parentId' => $department['IBLOCK_SECTION_ID'],
					'head' => $department['UF_HEAD'],
				];
		}

		$arResult = $arDepartment;
		foreach ($arDepartment as $key => $item) {
			$arResult[$key]['parent'] = trim($arResult[$item['parentId']]['name'], " \t");
		}

		return $arResult;
	}

	public static function getOldEnrollments()
	{
		$iblockId = \Bitrix\Iblock\IblockTable::getList(['filter' => ['CODE' => self::IBLOCK_CODE]])->Fetch()["ID"];

		$arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_*"];
		$arFilter = ["IBLOCK_ID" =>  $iblockId, "ACTIVE" => "Y"];
		$res = \CIBlockElement::GetList([], $arFilter, false, array("nPageSize" => 50), $arSelect);
		while ($ob = $res->GetNextElement()) {
			$arFields = $ob->GetFields();
			$arProperty = $ob->GetProperties();
			$arElement['NAME'] = $arFields['NAME'];
			foreach ($arProperty as $item) {
				$arElement[$item['CODE']] = $item['VALUE'];
			}
			$arElements[] = $arElement;
		}

		foreach ($arElements as $item) {
			$arResult[] = $item['ENROLLMENTID'];
		}
		return $arResult;
	}

	public static function addEnrollment($name, $properties = [])
	{
		$iblockId = \Bitrix\Iblock\IblockTable::getList(['filter' => ['CODE' => self::IBLOCK_CODE]])->Fetch()["ID"];
		$el = new \CIBlockElement;
		$arElement =
			[
				"IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
				"IBLOCK_ID"      =>  $iblockId,
				"PROPERTY_VALUES" => $properties,
				"NAME"           => $name,
				"ACTIVE"         => "Y",            // активен
			];

		if ($element_id = $el->Add($arElement)) {
			return $element_id;
		} else {
			return $el->LAST_ERROR;
		}
	}

	public static function getUserId($email)
	{
		$filter = ["EMAIL" => $email];
		$rsUser = \CUser::GetList(($by = "id"), ($order = "desc"), $filter);
		$arUser = $rsUser->Fetch();
		return $arUser['ID'];
	}
}
