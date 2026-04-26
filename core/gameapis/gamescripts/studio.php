<?php
	ob_start();
?>
-- Setup studio cmd bar & load core scripts

pcall(function() game:GetService("InsertService"):SetFreeModelUrl("http://zomium.xyz/Game/Tools/InsertAsset.ashx?type=fm&q=%s&pg=%d&rs=%d") end)
pcall(function() game:GetService("InsertService"):SetFreeDecalUrl("http://zomium.xyz/Game/Tools/InsertAsset.ashx?type=fd&q=%s&pg=%d&rs=%d") end)

pcall(function() game:GetService("ScriptInformationProvider"):SetAssetUrl("http://zomium.xyz/Asset/") end)
pcall(function() game:GetService("InsertService"):SetBaseSetsUrl("http://zomium.xyz/Game/Tools/InsertAsset.ashx?nsets=10&type=base") end)
pcall(function() game:GetService("InsertService"):SetUserSetsUrl("http://zomium.xyz/Game/Tools/InsertAsset.ashx?nsets=20&type=user&userid=%d") end)
pcall(function() game:GetService("InsertService"):SetCollectionUrl("http://zomium.xyz/Game/Tools/InsertAsset.ashx?sid=%d") end)
pcall(function() game:GetService("InsertService"):SetAssetUrl("http://zomium.xyz/Asset/?id=%d") end)
pcall(function() game:GetService("InsertService"):SetAssetVersionUrl("http://zomium.xyz/Asset/?assetversionid=%d") end)

pcall(function() game:GetService("SocialService"):SetFriendUrl("http://zomium.xyz/Game/LuaWebService/HandleSocialRequest.ashx?method=IsFriendsWith&playerid=%d&userid=%d") end)
pcall(function() game:GetService("SocialService"):SetBestFriendUrl("http://zomium.xyz/Game/LuaWebService/HandleSocialRequest.ashx?method=IsBestFriendsWith&playerid=%d&userid=%d") end)
pcall(function() game:GetService("SocialService"):SetGroupUrl("http://zomium.xyz/Game/LuaWebService/HandleSocialRequest.ashx?method=IsInGroup&playerid=%d&groupid=%d") end)
pcall(function() game:GetService("SocialService"):SetGroupRankUrl("http://zomium.xyz/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRank&playerid=%d&groupid=%d") end)
pcall(function() game:GetService("SocialService"):SetGroupRoleUrl("http://zomium.xyz/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRole&playerid=%d&groupid=%d") end)
pcall(function() game:GetService("GamePassService"):SetPlayerHasPassUrl("http://zomium.xyz/Game/GamePass/GamePassHandler.ashx?Action=HasPass&UserID=%d&PassID=%d") end)

pcall(function() game:GetService("MarketplaceService"):SetProductInfoUrl("https://zomium.xyz/marketplace/productinfo?assetId=%d") end)
pcall(function() game:GetService("MarketplaceService"):SetDevProductInfoUrl("http://zomium.xyz/marketplace/productDetails?productId=%d") end)
pcall(function() game:GetService("MarketplaceService"):SetPlayerOwnsAssetUrl("http://zomium.xyz/ownership/hasasset?userId=%d&assetId=%d") end)

pcall(function() game:GetService("ScriptContext"):AddCoreScript(2610, game:GetService("ScriptContext"), "StarterScript") end)

<?php
    function get_signature($script)
    {
        $signature = "";
        openssl_sign($script, $signature, file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../PrivateKey.pem"), OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }    

    header("Content-Type: text/plain");

    $script = "\r\n" . ob_get_clean();
    $signature = get_signature($script);

    echo "%". $signature . "%" . $script;
?>