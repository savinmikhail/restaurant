<?php

namespace app\models\definitions;

/**
 * @SWG\Definition(required={"user_email", "user_password"})
 *
 * @SWG\Property(property="user_login", type="string")
 * @SWG\Property(property="user_email", type="string")
 * @SWG\Property(property="user_password", type="string")
 * @SWG\Property(property="user_is_active", type="integer")
 * @SWG\Property(property="user_phone", type="string")
 * @SWG\Property(property="user_first_name", type="string")
 * @SWG\Property(property="user_last_name", type="string")
 * @SWG\Property(property="user_sur_name", type="string")
 * @SWG\Property(property="user_inn", type="string")
 * @SWG\Property(property="user_kpp", type="string")
 * @SWG\Property(property="user_company_name", type="string")
 * @SWG\Property(property="created", type="datetime")
 * @SWG\Property(property="updated", type="datetime")
 */
class User
{

}