<?php
include("slowFT.php");
################# test ##################
function test_phase_detection_with_various_amplitudes() {
    echo "=== 相位检测测试（不同幅度） ===\n\n";
    
    $count = 100;  // 数据点数
    $freq = 2;     // 测试频率
    
    // 测试不同的幅度值（各种正数）
    $test_amplitudes = [0.1, 0.5, 1, 1.5, 2, 2.5, 3, 5, 10, 0.01, 0.001, 100];
    
    // 测试不同的相位值
    $test_phases = [0, 0.125, 0.25, 0.375, 0.5, 0.625, 0.75, 0.875];
    
    echo "测试频率: $freq, 数据点数: $count\n";
    echo str_repeat("=", 80) . "\n";
    
    $all_success = true;
    $total_tests = 0;
    $passed_tests = 0;
    
    foreach ($test_amplitudes as $size) {
        echo "\n【幅度 = ${size}】\n";
        echo str_repeat("-", 80) . "\n";
        echo sprintf("%-15s %-15s %-20s %-10s\n", "真实相位", "检测相位", "误差", "状态");
        echo str_repeat("-", 80) . "\n";
        
        $phase_success = true;
        
        foreach ($test_phases as $true_phase) {
            // 用 customsin 生成数据
            $array = [];
            for ($x = 0; $x < $count; $x++) {
                $array[] = customsin($count, $freq, $true_phase, $size, $x);
            }
            
            // 检测相位
            $detected_phase = get_phase($array, $freq);
            
            // 计算误差（考虑周期性，相位在0-1范围内）
            $error = abs($detected_phase - $true_phase);
            if ($error > 0.5) {
                $error = 1 - $error;
            }
            
            $total_tests++;
            $passed = $error < 0.01;  // 允许1%的误差
            if ($passed) {
                $passed_tests++;
            } else {
                $phase_success = false;
                $all_success = false;
            }
            
            $status = $passed ? "✓ 通过" : "✗ 失败";
            echo sprintf("%-15.6f %-15.6f %-20.6f %-10s\n", 
                $true_phase, $detected_phase, $error, $status);
        }
        
        if ($phase_success) {
            echo "✓ 该幅度下所有相位检测通过\n";
        } else {
            echo "✗ 该幅度下存在相位检测失败\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "测试总结:\n";
    echo "总测试次数: $total_tests\n";
    echo "通过次数: $passed_tests\n";
    echo "成功率: " . ($passed_tests / $total_tests * 100) . "%\n";
    
    if ($all_success) {
        echo "✓ 所有幅度和相位组合测试通过！\n";
    } else {
        echo "✗ 部分测试失败，相位检测算法需要改进\n";
    }
}
test_phase_detection_with_various_amplitudes();
function test_amplitude_and_phase_detection() {
    echo "=== 综合测试：相位和幅度检测 ===\n\n";
    
    $count = 100;  // 数据点数
    
    // 测试不同的幅度值
    $test_amplitudes = [0.1, 0.5, 1, 1.5, 2, 2.5, 3, 5, 10, 0.01, 0.001, 100];
    
    // 测试不同的相位值
    $test_phases = [0, 0.125, 0.25, 0.375, 0.5, 0.625, 0.75, 0.875];
    
    // 测试不同的频率
    $test_frequencies = [1, 2, 3, 4, 5, floor(100/2)-1];
    
    echo str_repeat("=", 100) . "\n";
    echo sprintf("%-8s %-12s %-12s %-12s %-12s %-12s %-12s\n", 
        "频率", "真实幅度", "检测幅度", "幅度误差%", "真实相位", "检测相位", "相位误差");
    echo str_repeat("=", 100) . "\n";
    
    $total_tests = 0;
    $passed_tests = 0;
    $amplitude_passed = 0;
    $phase_passed = 0;
    
    foreach ($test_frequencies as $freq) {
        echo "\n【频率 = ${freq}】\n";
        echo str_repeat("-", 100) . "\n";
        
        foreach ($test_amplitudes as $true_size) {
            foreach ($test_phases as $true_phase) {
                // 生成测试数据
                $array = [];
                for ($x = 0; $x < $count; $x++) {
                    $array[] = customsin($count, $freq, $true_phase, $true_size, $x);
                }
                
                // 检测相位
                $detected_phase = get_phase($array, $freq);
                
                // 检测幅度（传入检测到的相位）
                $detected_size = get_size($array, $freq, $detected_phase);
                
                // 计算相位误差（考虑周期性）
                $phase_error = abs($detected_phase - $true_phase);
                if ($phase_error > 0.5) {
                    $phase_error = 1 - $phase_error;
                }
                
                // 计算幅度误差（百分比）
                $amplitude_error_percent = abs($detected_size - $true_size) / $true_size * 100;
                
                // 判断是否通过
                $phase_ok = $phase_error < 0.01;  // 相位误差<1%
                $amplitude_ok = $amplitude_error_percent < 5;  // 幅度误差<5%
                $passed = $phase_ok && $amplitude_ok;
                
                $total_tests++;
                if ($passed) $passed_tests++;
                if ($phase_ok) $phase_passed++;
                if ($amplitude_ok) $amplitude_passed++;
                
                // 只显示部分结果（避免输出过多）
                static $display_count = 0;
                if ($display_count < 50 || !$passed) {
                    $status = $passed ? "✓" : "✗";
                    echo sprintf("%-8d %-12.6f %-12.6f %-11.2f%% %-12.6f %-12.6f %-12.6f %s\n", 
                        $freq, $true_size, $detected_size, $amplitude_error_percent,
                        $true_phase, $detected_phase, $phase_error, $status);
                    $display_count++;
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "测试总结:\n";
    echo str_repeat("-", 100) . "\n";
    echo "总测试次数: $total_tests\n";
    echo "通过次数: $passed_tests\n";
    echo "成功率: " . ($passed_tests / $total_tests * 100) . "%\n";
    echo "相位检测通过率: " . ($phase_passed / $total_tests * 100) . "%\n";
    echo "幅度检测通过率: " . ($amplitude_passed / $total_tests * 100) . "%\n";
    
    if ($passed_tests == $total_tests) {
        echo "✓ 所有测试通过！相位和幅度检测都非常准确！\n";
    } elseif ($phase_passed == $total_tests && $amplitude_passed < $total_tests) {
        echo "⚠ 相位检测完美，但幅度检测在某些情况下需要改进\n";
    } elseif ($phase_passed < $total_tests && $amplitude_passed == $total_tests) {
        echo "⚠ 幅度检测完美，但相位检测在某些情况下需要改进\n";
    } else {
        echo "✗ 需要同时改进相位和幅度检测算法\n";
    }
    
    return [
        'total_tests' => $total_tests,
        'passed_tests' => $passed_tests,
        'phase_passed' => $phase_passed,
        'amplitude_passed' => $amplitude_passed,
        'success_rate' => ($passed_tests / $total_tests * 100)
    ];
}

// 运行综合测试
$results = test_amplitude_and_phase_detection();

// 额外的精细测试：小幅度和极端情况
function test_extreme_cases() {
    echo "\n\n=== 极端情况测试 ===\n\n";
    
    $count = 100;
    $freq = 2;
    
    $extreme_cases = [
        ['name' => '极小幅度', 'size' => 0.0001, 'phase' => 0.3],
        ['name' => '巨大幅度', 'size' => 1000000, 'phase' => 0.7],
        ['name' => '边界相位0', 'size' => 1, 'phase' => 0],
        ['name' => '边界相位0.999', 'size' => 1, 'phase' => 0.999],
        ['name' => '噪声数据', 'size' => 1, 'phase' => 0.5, 'noise' => true],
    ];
    
    echo str_repeat("=", 80) . "\n";
    echo sprintf("%-20s %-15s %-15s %-15s %-15s\n", 
        "测试场景", "真实幅度", "检测幅度", "真实相位", "检测相位");
    echo str_repeat("=", 80) . "\n";
    
    foreach ($extreme_cases as $case) {
        $array = [];
        for ($x = 0; $x < $count; $x++) {
            $value = customsin($count, $freq, $case['phase'], $case['size'], $x);
            // 如果是噪声测试，添加随机噪声
            if (isset($case['noise']) && $case['noise']) {
                $value += (rand(-100, 100) / 100) * 0.1;  // 10%噪声
            }
            $array[] = $value;
        }
        
        $detected_phase = get_phase($array, $freq);
        $detected_size = get_size($array, $freq, $detected_phase);
        
        $phase_error = abs($detected_phase - $case['phase']);
        if ($phase_error > 0.5) $phase_error = 1 - $phase_error;
        
        $size_error_percent = abs($detected_size - $case['size']) / $case['size'] * 100;
        
        $status = ($phase_error < 0.01 && $size_error_percent < 5) ? "✓" : "✗";
        
        echo sprintf("%-20s %-15.6f %-15.6f %-15.6f %-15.6f %s\n", 
            $case['name'], $case['size'], $detected_size, $case['phase'], $detected_phase, $status);
        
        if ($phase_error >= 0.01 || $size_error_percent >= 5) {
            echo sprintf("  └─ 相位误差: %.6f, 幅度误差: %.2f%%\n", $phase_error, $size_error_percent);
        }
    }
}

// 运行极端情况测试
test_extreme_cases();




function test_multi_frequency_transform() {
    echo "=== 多频率混合信号傅里叶变换测试 ===\n\n";
    
    $count = 100;  // 数据点数
    
    // 测试用例：不同频率、幅度、相位的组合
    $test_cases = [
        [
            'name' => '两个频率叠加',
            'components' => [
                ['freq' => 2, 'size' => 3.0, 'phase' => 0.2],
                ['freq' => 5, 'size' => 1.5, 'phase' => 0.7]
            ]
        ],
        [
            'name' => '三个频率叠加',
            'components' => [
                ['freq' => 1, 'size' => 5.0, 'phase' => 0.1],
                ['freq' => 3, 'size' => 2.0, 'phase' => 0.4],
                ['freq' => 7, 'size' => 1.0, 'phase' => 0.8]
            ]
        ],
        [
            'name' => '主频+谐波',
            'components' => [
                ['freq' => 2, 'size' => 4.0, 'phase' => 0.0],
                ['freq' => 4, 'size' => 2.0, 'phase' => 0.3],
                ['freq' => 6, 'size' => 1.0, 'phase' => 0.6]
            ]
        ],
        [
            'name' => '强弱对比明显',
            'components' => [
                ['freq' => 3, 'size' => 10.0, 'phase' => 0.5],
                ['freq' => 8, 'size' => 0.5, 'phase' => 0.9]
            ]
        ],
        [
            'name' => '四个频率复杂混合',
            'components' => [
                ['freq' => 2, 'size' => 2.5, 'phase' => 0.15],
                ['freq' => 4, 'size' => 1.8, 'phase' => 0.45],
                ['freq' => 6, 'size' => 1.2, 'phase' => 0.75],
                ['freq' => 9, 'size' => 0.8, 'phase' => 0.95]
            ]
        ]
    ];
    
    $all_tests_passed = true;
    
    foreach ($test_cases as $case_index => $case) {
        echo "\n" . str_repeat("=", 100) . "\n";
        echo "测试用例 " . ($case_index + 1) . ": " . $case['name'] . "\n";
        echo str_repeat("=", 100) . "\n";
        
        // 生成混合信号
        $signal = [];
        for ($x = 0; $x < $count; $x++) {
            $value = 0;
            foreach ($case['components'] as $comp) {
                $value += customsin($count, $comp['freq'], $comp['phase'], $comp['size'], $x);
            }
            $signal[] = $value;
        }
        
        // 显示信号统计信息
        echo "\n原始信号统计:\n";
        echo sprintf("  均值: %.6f\n", array_sum($signal) / $count);
        echo sprintf("  最大值: %.6f\n", max($signal));
        echo sprintf("  最小值: %.6f\n", min($signal));
        echo sprintf("  标准差: %.6f\n", sqrt(array_sum(array_map(function($x) use ($signal) {
            $mean = array_sum($signal) / count($signal);
            return pow($x - $mean, 2);
        }, $signal)) / count($signal)));
        
        // 应用傅里叶变换
        echo "\n开始傅里叶变换分析...\n";
        $ft_result = slowFT($signal);
        
        // 显示检测结果
        echo "\n检测到的频率分量:\n";
        echo str_repeat("-", 100) . "\n";
        echo sprintf("%-10s %-15s %-15s %-15s %-15s %-10s\n", 
            "频率", "真实幅度", "检测幅度", "真实相位", "检测相位", "匹配");
        echo str_repeat("-", 100) . "\n";
        
        $detected_count = 0;
        $matched_count = 0;
        
        // 对每个原始分量，查找最接近的检测结果
        foreach ($case['components'] as $comp) {
            $best_match = null;
            $best_error = PHP_FLOAT_MAX;
            
            for ($freq = 1; $freq < floor($count/2) - 1; $freq++) {
                if (isset($ft_result[$freq])) {
                    $size_error = abs($ft_result[$freq][0] - $comp['size']) / $comp['size'];
                    $phase_error = abs($ft_result[$freq][1] - $comp['phase']);
                    if ($phase_error > 0.5) $phase_error = 1 - $phase_error;
                    
                    $total_error = $size_error + $phase_error;
                    
                    if ($total_error < $best_error) {
                        $best_error = $total_error;
                        $best_match = ['freq' => $freq, 'size' => $ft_result[$freq][0], 'phase' => $ft_result[$freq][1]];
                    }
                }
            }
            
            if ($best_match) {
                $matched = false;
                $size_error_percent = abs($best_match['size'] - $comp['size']) / $comp['size'] * 100;
                $phase_error = abs($best_match['phase'] - $comp['phase']);
                if ($phase_error > 0.5) $phase_error = 1 - $phase_error;
                
                $matched = ($size_error_percent < 10 && $phase_error < 0.05);
                if ($matched) $matched_count++;
                
                echo sprintf("%-10d %-15.6f %-15.6f %-15.6f %-15.6f %-10s\n", 
                    $best_match['freq'], $comp['size'], $best_match['size'], 
                    $comp['phase'], $best_match['phase'], 
                    $matched ? "✓" : "✗");
                
                if (!$matched) {
                    echo sprintf("  └─ 幅度误差: %.2f%%, 相位误差: %.6f\n", $size_error_percent, $phase_error);
                }
            }
            $detected_count++;
        }
        
        // 显示所有检测到的频率（包括可能误检的）
        echo "\n所有检测到的频率分量:\n";
        echo str_repeat("-", 100) . "\n";
        echo sprintf("%-10s %-20s %-20s\n", "频率", "检测幅度", "检测相位");
        echo str_repeat("-", 100) . "\n";
        
        for ($freq = 1; $freq < floor($count/2) - 1; $freq++) {
            if (isset($ft_result[$freq]) && $ft_result[$freq][0] > 0.1) {  // 只显示幅度大于0.1的
                echo sprintf("%-10d %-20.6f %-20.6f\n", 
                    $freq, $ft_result[$freq][0], $ft_result[$freq][1]);
            }
        }
        
        $case_passed = ($matched_count == count($case['components']));
        $all_tests_passed = $all_tests_passed && $case_passed;
        
        echo "\n测试结果: " . ($case_passed ? "✓ 通过" : "✗ 失败");
        echo sprintf(" (匹配: %d/%d)\n", $matched_count, count($case['components']));
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "总体测试结果: " . ($all_tests_passed ? "✓ 所有测试通过！" : "✗ 部分测试失败，需要改进算法") . "\n";
    
    return $all_tests_passed;
}

// 额外测试：添加噪声的多频率信号
function test_noisy_multi_frequency() {
    echo "\n\n=== 带噪声的多频率信号测试 ===\n\n";
    
    $count = 100;
    $noise_levels = [0, 0.05, 0.1, 0.2, 0.5];  // 不同噪声级别
    
    $components = [
        ['freq' => 2, 'size' => 3.0, 'phase' => 0.2],
        ['freq' => 5, 'size' => 2.0, 'phase' => 0.6],
        ['freq' => 8, 'size' => 1.0, 'phase' => 0.9]
    ];
    
    echo "测试信号: 三个频率分量 (2, 5, 8)\n";
    echo str_repeat("=", 100) . "\n";
    echo sprintf("%-15s %-20s %-20s %-20s %-10s\n", 
        "噪声级别", "平均幅度误差", "平均相位误差", "检测成功率", "状态");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($noise_levels as $noise_level) {
        // 生成带噪声的混合信号
        $signal = [];
        for ($x = 0; $x < $count; $x++) {
            $value = 0;
            foreach ($components as $comp) {
                $value += customsin($count, $comp['freq'], $comp['phase'], $comp['size'], $x);
            }
            // 添加高斯噪声
            $noise = (rand(-10000, 10000) / 10000) * $noise_level * array_sum(array_column($components, 'size'));
            $signal[] = $value + $noise;
        }
        
        // 应用变换
        $ft_result = slowFT($signal);
        
        // 评估检测精度
        $total_size_error = 0;
        $total_phase_error = 0;
        $detected_count = 0;
        
        foreach ($components as $comp) {
            if (isset($ft_result[$comp['freq']])) {
                $size_error = abs($ft_result[$comp['freq']][0] - $comp['size']) / $comp['size'] * 100;
                $phase_error = abs($ft_result[$comp['freq']][1] - $comp['phase']);
                if ($phase_error > 0.5) $phase_error = 1 - $phase_error;
                
                $total_size_error += $size_error;
                $total_phase_error += $phase_error;
                $detected_count++;
            }
        }
        
        $avg_size_error = $detected_count > 0 ? $total_size_error / $detected_count : 100;
        $avg_phase_error = $detected_count > 0 ? $total_phase_error / $detected_count : 1;
        $success_rate = $detected_count / count($components) * 100;
        
        $status = ($success_rate >= 80 && $avg_size_error < 15) ? "✓ 可用" : 
                  ($success_rate >= 50 ? "⚠ 部分可用" : "✗ 失败");
        
        echo sprintf("%-15.2f %-20.2f%% %-20.6f %-19.1f%% %-10s\n", 
            $noise_level, $avg_size_error, $avg_phase_error, $success_rate, $status);
    }
    
    echo "\n说明: 噪声级别为相对于信号幅度的比例\n";
}

// 可视化函数：绘制原始信号和重建信号对比
function visualize_reconstruction($signal, $ft_result, $count) {
    echo "\n信号重建对比（前50个点）:\n";
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-10s %-15s %-15s %-15s\n", "采样点", "原始信号", "重建信号", "误差");
    echo str_repeat("-", 80) . "\n";
    
    $total_error = 0;
    
    for ($x = 0; $x < min(50, $count); $x++) {
        // 重建信号：使用检测到的频率分量
        $reconstructed = 0;
        for ($freq = 1; $freq < floor($count/2) - 1; $freq++) {
            if (isset($ft_result[$freq])) {
                $reconstructed += customsin($count, $freq, $ft_result[$freq][1], $ft_result[$freq][0], $x);
            }
        }
        
        $error = abs($signal[$x] - $reconstructed);
        $total_error += $error;
        
        // 简单的ASCII艺术图
        $bar_length = min(50, abs($signal[$x]) * 5);
        $signal_bar = $signal[$x] > 0 ? str_repeat("█", $bar_length) : str_repeat("░", $bar_length);
        
        echo sprintf("%-10d %-15.6f %-15.6f %-15.6f %s\n", 
            $x, $signal[$x], $reconstructed, $error, $signal_bar);
    }
    
    echo "\n平均重建误差: " . ($total_error / min(50, $count)) . "\n";
}

// 主测试函数
function run_comprehensive_multi_freq_test() {
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                    多频率傅里叶变换综合测试系统                            ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
    
    
    // 运行所有测试
    $test1 = test_multi_frequency_transform();
    test_noisy_multi_frequency();
    
    // 可选：测试特定用例并显示可视化
    echo "\n\n=== 详细分析示例：三频率混合信号 ===\n";
    $count = 100;
    $test_signal = [];
    for ($x = 0; $x < $count; $x++) {
        $test_signal[] = customsin($count, 2, 0.2, 3.0, $x) + 
                        customsin($count, 5, 0.6, 2.0, $x) + 
                        customsin($count, 8, 0.9, 1.0, $x);
    }
    
    $ft_result = slowFT($test_signal);
    visualize_reconstruction($test_signal, $ft_result, $count);
    
    return $test1;
}

// 运行综合测试
run_comprehensive_multi_freq_test();