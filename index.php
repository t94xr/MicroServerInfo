<?php
/*
 * Script Name:  Micro Server Status
 * LICENCE:      MIT License
 *
 * 
 * Put theis line in /etc/sudoers (replace t94xr with your username)
 *      www-data ALL=(ALL) NOPASSWD: /usr/sbin/smartctl
 * 
 * smartctl:
 *   `smartctl` is part of the **smartmontools** package. To install it, run:
 *    sudo apt install smartmontools
 *
 * tuptime:
 *    The `tuptime` command is used to fetch uptime statistics. To install it, run:
 *    sudo apt install tuptime
 * 
 *  Functions of this script may not work
 * 
 */
?>
<!--
MIT License

Copyright (c) 2025 Cameron Walker

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
-->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo php_uname('n'); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://rsms.me/inter/inter.css');
    :root {
  font-feature-settings: 'liga' 1, 'calt' 1; /* fix for Chrome */
}
@supports (font-variation-settings: normal) {
  :root { font-family: InterVariable, sans-serif; }
}
    body {
      font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    .storj-logo {
      max-width: 50px;
      opacity: 0.5;
      transition: opacity 0.3s ease;
    }
    .storj-logo:hover {
      opacity: 1.0;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <?php
    function getTemperatureBadge($temp) {
      if ($temp < 25) {
        return '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset">' . $temp . '°C</span>';
      } elseif ($temp >= 25 && $temp <= 40) {
        return '<span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">' . $temp . '°C</span>';
      } elseif ($temp > 40 && $temp <= 50) {
        return '<span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-700/10 ring-inset">' . $temp . '°C</span>';
      } else {
        return '<span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-red-600/10 ring-inset">' . $temp . '°C</span>';
      }
    }

    $currentUptime = trim(shell_exec("tuptime | grep 'Current uptime' | awk -F':' '{print \$2}' | sed 's/ since.*//g' | xargs"));
    $recordUptime = trim(shell_exec("tuptime | grep 'Longest uptime' | awk -F':' '{print \$2}' | sed 's/  from.*//' | xargs"));
    $averageUptime = trim(shell_exec("tuptime | grep 'Average uptime' | awk -F':' '{print \$2}' | xargs"));
    $systemLife = trim(shell_exec("tuptime | grep 'System life' | awk -F':' '{print \$2}' | xargs"));
    $availability = trim(shell_exec("tuptime | grep 'System uptime' | awk -F':' '{print \$2}' | sed 's/ =.*//g' | xargs"));

    // Update for sdb (previously sdc)
    $sdaTemp = (int) trim(shell_exec("sudo /usr/sbin/smartctl -A /dev/sda | grep -i temperature | awk '{print \$10}'"));
    $sdbTemp = (int) trim(shell_exec("sudo /usr/sbin/smartctl -A /dev/sdb | grep -i temperature | awk '{print \$10}'"));
    $sdaBadge = getTemperatureBadge($sdaTemp);
    $sdbBadge = getTemperatureBadge($sdbTemp);
    //$sdbBadge = getTemperatureBadge(56); // This one is used for testing


  // Example: Ensure that the availability percentage is calculated correctly
  $availabilityPercentage = (float) trim(shell_exec("tuptime | grep 'System uptime' | awk -F':' '{print \$2}' | sed 's/ =.*//g' | xargs"));
    //$availabilityPercentage = '50%'; // test var to ensure the border color change.
  // Determine the border color based on the availability percentage
  if ($availabilityPercentage >= 99.99) {
      $borderColorClass = "border-yellow-500"; // Excellent -> Yellow
  } elseif ($availabilityPercentage >= 99.9) {
      $borderColorClass = "border-green-500"; // Good -> Green
  } elseif ($availabilityPercentage >= 99.0) {
      $borderColorClass = "border-blue-500"; // Acceptable -> Blue
  } else {
      $borderColorClass = "border-red-500"; // Low -> Red
  }

?>

  <div class="max-w-[90ch] mx-auto mt-10 p-6 bg-white shadow-xl rounded-md border border-gray-300 space-y-8 relative">

    <div class="text-left mb-8">
      <h1 class="text-3xl font-bold uppercase"><?php echo php_uname('n'); ?></h1>
    </div>

    <div class="rounded-lg bg-gray-50 p-4 shadow-sm border border-gray-300 flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
      <div class="flex-1">
        <p class="text-sm text-gray-600 font-medium uppercase mb-2">Uptime</p>
        <p class="text-4xl font-extrabold text-gray-800 mb-2"><?php echo preg_replace('/\s\d+s$/', '', $currentUptime); ?></p>
      </div>

      <div class="w-full md:w-[25%] bg-white p-3 border-2 <?php echo $borderColorClass; ?> rounded-lg shadow-sm flex flex-col items-center justify-center">
        <p class="text-4xl font-bold text-gray-800"><?php echo $availability; ?></p>
        <p class="text-sm text-gray-500">Availability</p>
      </div>

    </div>



    <div class="max-w-[90ch] mx-auto mt-10 p-6 bg-gray-50 rounded-md border border-gray-300 space-y-8 relative">
    <div class="flex justify-between space-x-6">
        <!-- First Column -->
        <div class="flex flex-col items-center">
            <p class="text-xl font-semibold text-gray-800"><?php echo preg_replace('/\s\d+s$/', '', $recordUptime); ?></p>
            <p class="text-sm text-gray-600 mt-2">Record</p>
        </div>

        <!-- Second Column -->
        <div class="flex flex-col items-center">
            <p class="text-xl font-semibold text-gray-800"><?php echo preg_replace('/\s\d+s$/', '', $averageUptime); ?></p>
            <p class="text-sm text-gray-600 mt-2">Average</p>
        </div>

        <!-- Third Column -->
        <div class="flex flex-col items-center">
            <p class="text-xl font-semibold text-gray-800"><?php echo preg_replace('/\s\d+s$/', '', $systemLife); ?></p>
            <p class="text-sm text-gray-600 mt-2">System Life</p>
        </div>
    </div>
</div>



<?php
  // Function to get SMART status and return color
  function getSmartStatus($drive) {
    $status = trim(shell_exec("sudo /usr/sbin/smartctl -H /dev/$drive | grep 'SMART overall-health self-assessment test result' | awk '{print $6}'"));
    if ($status == "PASSED") {
      return 'border-green-500';  // Green border for PASSED
    } else {
      return 'border-red-500';  // Red border for FAILED
    }
  }

  // Get SMART status for sda and sdb
  $sdaStatusClass = getSmartStatus('sda');
  $sdbStatusClass = getSmartStatus('sdb');
?>

<div class="flex flex-col md:flex-row gap-6">
  <?php
    $sdaUsedPercent = (int) trim(shell_exec("df | grep sda1 | awk '{print \$5}' | tr -d '%'"));
    $sdaFree = trim(shell_exec("df -h | grep sda1 | awk '{print \$4}'"));
    $sdaTotal = trim(shell_exec("df -h | grep sda1 | awk '{print \$2}'"));
    $sdaBarColor = $sdaUsedPercent > 80 ? 'bg-red-600' : 'bg-blue-500';
  ?>
  <div class="bg-gray-50 p-4 border border-gray-300 rounded-lg shadow-sm flex-1 flex items-start space-x-4 border-t-4 <?php echo $sdaStatusClass; ?>">
    <img src="https://cdn.osxdaily.com/wp-content/uploads/2012/01/hard-drive.jpg" alt="Drive Icon" class="w-12 h-12 object-contain ml-auto mt-2">
    <div class="flex-1">
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-600 font-medium">/dev/sda1</div>
        <?php echo $sdaBadge; ?>
      </div>
      <div class="w-full bg-gray-200 border border-gray-300 h-4 mb-1 mt-1">
        <div class="<?php echo $sdaBarColor; ?> h-4" style="width: <?php echo $sdaUsedPercent; ?>%;"></div>
      </div>
      <div class="text-xs text-gray-800"><?php echo "$sdaFree free of $sdaTotal"; ?></div>
    </div>
  </div>

  <?php
    $sdbUsedPercent = (int) trim(shell_exec("df | grep sdb1 | awk '{print \$5}' | tr -d '%'"));
    $sdbFree = trim(shell_exec("df -h | grep sdb1 | awk '{print \$4}'"));
    $sdbTotal = trim(shell_exec("df -h | grep sdb1 | awk '{print \$2}'"));
    $sdbBarColor = $sdbUsedPercent > 80 ? 'bg-red-600' : 'bg-blue-500';
  ?>
  <div class="bg-gray-50 p-4 border border-gray-300 rounded-lg shadow-sm flex-1 flex items-start space-x-4 border-t-4 <?php echo $sdbStatusClass; ?>">
    <img src="https://cdn.osxdaily.com/wp-content/uploads/2012/01/hard-drive.jpg" alt="Drive Icon" class="w-12 h-12 object-contain ml-auto mt-2">
    <div class="flex-1">
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-600 font-medium">/dev/sdb1</div>
        <?php echo $sdbBadge; ?>
      </div>
      <div class="w-full bg-gray-200 border border-gray-300 h-4 mb-1 mt-1">
        <div class="<?php echo $sdbBarColor; ?> h-4" style="width: <?php echo $sdbUsedPercent; ?>%;"></div>
      </div>
      <div class="text-xs text-gray-800"><?php echo "$sdbFree free of $sdbTotal"; ?></div>
    </div>
  </div>
</div>

    <footer class="border-t border-gray-300 mt-5 pt-1 text-sm text-gray-400">
        <a href="">Micro Server Status</a>
    </footer>

  </div>
</body>
</html>
