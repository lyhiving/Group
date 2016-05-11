<?php

namespace src\Web\Controller\Pet;

use src\Web\Controller\BaseController;
use Request;
use src\Web\Common\SimpleValidator;

class IndexController extends BaseController
{
    public function addPetAction(Request $request)
    {
        $token = $request -> request -> get('token');
        $uid = $this -> isLogin($token);
        if (!$uid) return $this -> createJsonResponse('', '请登录', 2);

        $pet = $request -> request -> all();

        if (!SimpleValidator::nickname($pet['pname'])) {
            return $this->createJsonResponse('', '宠物名3-20位，一个中文为2个字符', 0);
        }

        if (!in_array($pet['sex'], ['gong', 'mu'])) {
            return $this->createJsonResponse('', '宠物性别错误', 0);
        }

        if (!in_array($pet['type'], ['dog', 'cat', 'reptile', 'other'])) {
            return $this->createJsonResponse('', '宠物类型错误', 0);
        }

        $res = D('Pet') -> addPet($uid, $pet);
        if ($res) return $this -> createJsonResponse(D('Pet') -> getPet($res), '添加成功', 1);
        return $this -> createJsonResponse('', '添加失败', 0);
    }

    public function editPetAction(Request $request)
    {
        $token = $request -> request -> get('token');
        $uid = $this -> isLogin($token);
        if (!$uid) return $this -> createJsonResponse('', '请登录', 2);

        $pet = $request -> request -> all();

        $exist = D('Pet') -> getPet($pet['pid']);
        if (!$exist) return $this -> createJsonResponse('', '宠物不存在', 0);
        if ($exist['uid'] != $uid) return $this -> createJsonResponse('', '没有权限编辑', 0);

        if (!SimpleValidator::nickname($pet['pname'])) {
            return $this->createJsonResponse('', '宠物名3-20位，一个中文为2个字符', 0);
        }

        if (!in_array($pet['sex'], ['gong', 'mu'])) {
            return $this->createJsonResponse('', '宠物性别错误', 0);
        }

        if (!in_array($pet['type'], ['dog', 'cat', 'reptile', 'other'])) {
            return $this->createJsonResponse('', '宠物类型错误', 0);
        }

        $res = D('Pet') -> editPet($pet['pid'], $pet);
        if ($res) return $this -> createJsonResponse(D('Pet') -> getPet($pet['pid']), '编辑成功', 1);
        return $this -> createJsonResponse('', '编辑失败', 0);
    }

    public function deletePetAction(Request $request)
    {
        $token = $request -> request -> get('token');
        $uid = $this -> isLogin($token);
        if (!$uid) return $this -> createJsonResponse('', '请登录', 2);

        $pet = $request -> request -> all();

        $exist = D('Pet') -> getPet($pet['pid']);
        if (!$exist) return $this -> createJsonResponse('', '宠物不存在', 0);
        if ($exist['uid'] != $uid) return $this -> createJsonResponse('', '没有权限编辑', 0);

        $res = D('Pet') -> deletePet($pet['pid']);
        if ($res) return $this -> createJsonResponse('', '删除成功', 1);
        return $this -> createJsonResponse('', '删除失败', 0);
    }

    public function setAvatarAction(Request $request)
    {
        $info = $request -> request -> all();
        $uid = $this -> isLogin($info['token']);
        if (!$uid) return $this -> createJsonResponse('', '请登录', 2);

        $pid = $request -> request -> get('pid');
        $exist = D('Pet') -> getPet($pid);
        if (!$exist) return $this -> createJsonResponse('', '宠物不存在', 0);
        if ($exist['uid'] != $uid) return $this -> createJsonResponse('', '没有权限编辑', 0);

        $file = $request->files->get('avatar');
        $filenamePrefix = "pet_{$pid}_";

        $hash = substr(md5($filenamePrefix . time()), -8);
        $ext = $file -> getClientOriginalExtension();
        if ($ext) {
            $ext = '.' . $ext;
        }

        $fileName = $filenamePrefix . $hash . $ext;

        $file = $file -> move(__ROOT__."asset/public/avatar", $fileName);

        $img = \Intervention\Image\ImageManagerStatic::make("asset/public/avatar/".$fileName);
        // resize image instance
        $img->resize(200, 200);
        // save image in desired format
        $img->save(__ROOT__."asset/public/avatar/".$filenamePrefix . $hash . '2X2' . $ext);

        D('Pet') -> updatePetAvatar($fileName, $pid);
        $pet = D('Pet') -> getPet($pid);
        return $this -> createJsonResponse($pet, '头像更新成功', 1);
    }
}
