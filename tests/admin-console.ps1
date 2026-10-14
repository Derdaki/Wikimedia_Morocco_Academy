param([string] $PhpBinary = 'php')

$ErrorActionPreference = 'Stop'
if (!$env:DB_DATABASE -or !$env:DB_DATABASE.EndsWith('_test')) { throw 'Use a disposable DB_DATABASE ending in _test.' }
Set-Location -LiteralPath (Split-Path -Parent $PSScriptRoot)
$OutputEncoding = [System.Text.UTF8Encoding]::new($false)
$global:wmmaConsoleFixture = @{
    Name = 'Console ' + [char]0x00e9
    Email = 'console-' + [Guid]::NewGuid().ToString('N') + '@example.test'
    Password = [Guid]::NewGuid().ToString('N')
}

function Read-Host {
    param([string] $Prompt, [switch] $AsSecureString)
    if ($Prompt -match 'password|^Password') {
        if (!$AsSecureString) { throw 'Password prompt did not request secure input.' }
        return ConvertTo-SecureString $global:wmmaConsoleFixture.Password -AsPlainText -Force
    }
    if ($Prompt -eq 'Name') { return $global:wmmaConsoleFixture.Name }
    if ($Prompt -match 'email|^Email') { return $global:wmmaConsoleFixture.Email }
    if ($Prompt -like 'Role*') { return 'editor' }
    throw 'Unexpected account prompt.'
}

$verify = @'
require 'app/bootstrap.php';
$data = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$user = App\Models\User::findByEmail($data['Email']);
if (!$user || $user['name'] !== $data['Name'] || $user['role'] !== 'editor' ||
    !password_verify($data['Password'], $user['password_hash'])) exit(1);
'@
$cleanup = @'
require 'app/bootstrap.php';
$email = trim(stream_get_contents(STDIN));
$stmt = App\Core\Database::pdo()->prepare('DELETE FROM users WHERE email = ?');
$stmt->execute([$email]);
'@
try {
    $output = & ./scripts/admin-account.ps1 -Action create -PhpBinary $PhpBinary 2>&1
    if ($LASTEXITCODE -ne 0 -or ($output | Out-String).Contains($global:wmmaConsoleFixture.Password)) { throw 'Secure create helper failed or echoed a password.' }
    $global:wmmaConsoleFixture | ConvertTo-Json -Compress | & $PhpBinary -r $verify
    if ($LASTEXITCODE -ne 0) { throw 'Created account did not match its inputs.' }
    Write-Output 'PASS Windows helper requests secure password input, preserves UTF-8, and creates an Editor.'
    $global:wmmaConsoleFixture.Password = [Guid]::NewGuid().ToString('N')
    $output = & ./scripts/admin-account.ps1 -Action reset -PhpBinary $PhpBinary 2>&1
    if ($LASTEXITCODE -ne 0 -or ($output | Out-String).Contains($global:wmmaConsoleFixture.Password)) { throw 'Secure reset helper failed or echoed a password.' }
    $global:wmmaConsoleFixture | ConvertTo-Json -Compress | & $PhpBinary -r $verify
    if ($LASTEXITCODE -ne 0) { throw 'Reset did not update only the password.' }
    Write-Output 'PASS Windows helper resets the password while preserving identity and Editor role.'
} finally {
    $global:wmmaConsoleFixture.Email | & $PhpBinary -r $cleanup
    Remove-Variable wmmaConsoleFixture -Scope Global -ErrorAction SilentlyContinue
}
