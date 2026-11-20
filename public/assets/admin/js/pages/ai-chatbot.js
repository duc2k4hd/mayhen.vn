/**
 * Mây Hẹn Admin — Intelligence Engine Interactions & Client-Server Chunked Self-Learning Runner
 * Pure Vanilla ES Module — Zero external libraries, strictly XSS-safe (textContent only).
 * Thiết kế chịu tải cho Shared Hosting yếu: chạy tuần tự từng micro-step < 10s, nghỉ nhịp nhường CPU cho khách,
 * tự hồi phục khi rớt mạng (exponential backoff) và tự nối lại tiến độ nếu F5.
 */

const initConfirmForms = () => {
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const msg = form.getAttribute('data-confirm-delete') || 'Bạn chắc chắn muốn thực hiện thao tác này?';
            if (!window.confirm(msg)) {
                event.preventDefault();
            }
        });
    });
};

const initCleanUiDrawers = () => {
    // 1. Nút mở/đóng form Thêm kiến thức mới
    const createCard = document.getElementById('mayhenAdminTrainFormCard');
    const openNewBtn = document.getElementById('mayhenOpenNewKnowledgeBtn');
    const emptyAddBtn = document.getElementById('mayhenEmptyAddBtn');
    const closeNewBtn = document.getElementById('mayhenCloseNewKnowledgeBtn');
    const titleInput = document.getElementById('mayhenAdminTrainTitle');

    const toggleCreateCard = (show) => {
        if (!createCard) {
            return;
        }
        createCard.hidden = !show;
        if (show) {
            createCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            titleInput?.focus();
        }
    };

    openNewBtn?.addEventListener('click', () => toggleCreateCard(createCard?.hidden ?? true));
    emptyAddBtn?.addEventListener('click', () => toggleCreateCard(true));
    closeNewBtn?.addEventListener('click', () => toggleCreateCard(false));

    // 2. Nút mở/đóng khung Sửa gọn trên từng thẻ kiến thức
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-toggle-target]');
        if (!trigger) {
            return;
        }
        const targetId = trigger.getAttribute('data-toggle-target');
        const box = targetId ? document.getElementById(targetId) : null;
        if (!box) {
            return;
        }
        box.hidden = !box.hidden;
        if (!box.hidden) {
            const firstInput = box.querySelector('input[type="text"], textarea');
            firstInput?.focus();
        }
    });

    // 3. 3 Nút Preset chọn nhanh chế độ AI Tự Học (1 chạm cho người mới)
    const presetContainer = document.getElementById('mayhenRunnerPresets');
    const roundsEl = document.getElementById('mayhenRunnerRounds');
    const perTopicEl = document.getElementById('mayhenRunnerPerTopic');
    const cooldownEl = document.getElementById('mayhenRunnerCooldown');

    presetContainer?.addEventListener('click', (event) => {
        const card = event.target.closest('.mayhen_admin_ai_preset_card');
        if (!card) {
            return;
        }
        presetContainer.querySelectorAll('.mayhen_admin_ai_preset_card').forEach((el) => {
            el.classList.remove('mayhen_admin_ai_preset_active');
        });
        card.classList.add('mayhen_admin_ai_preset_active');

        if (roundsEl && card.dataset.presetRounds) {
            roundsEl.value = card.dataset.presetRounds;
        }
        if (perTopicEl && card.dataset.presetPerTopic) {
            perTopicEl.value = card.dataset.presetPerTopic;
        }
        if (cooldownEl && card.dataset.presetCooldown) {
            cooldownEl.value = card.dataset.presetCooldown;
        }
    });
};

const initChunkedLearningRunner = () => {
    const container = document.getElementById('mayhenAdminChunkedRunner');
    if (!container) {
        return;
    }

    const startUrl = container.dataset.startUrl || '';
    const stepTpl = container.dataset.stepUrlTemplate || '';
    const pauseTpl = container.dataset.pauseUrlTemplate || '';
    const resumeTpl = container.dataset.resumeUrlTemplate || '';
    const cancelTpl = container.dataset.cancelUrlTemplate || '';

    const statusBadge = document.getElementById('mayhenRunnerStatusBadge');
    const perTopicEl = document.getElementById('mayhenRunnerPerTopic');
    const roundsEl = document.getElementById('mayhenRunnerRounds');
    const cooldownEl = document.getElementById('mayhenRunnerCooldown');

    const startBtn = document.getElementById('mayhenRunnerStartBtn');
    const pauseBtn = document.getElementById('mayhenRunnerPauseBtn');
    const resumeBtn = document.getElementById('mayhenRunnerResumeBtn');
    const cancelBtn = document.getElementById('mayhenRunnerCancelBtn');
    const refreshBtn = document.getElementById('mayhenRunnerRefreshBtn');

    const progressPanel = document.getElementById('mayhenRunnerProgressPanel');
    const idText = document.getElementById('mayhenRunnerIdText');
    const stepText = document.getElementById('mayhenRunnerStepText');
    const percentText = document.getElementById('mayhenRunnerPercentText');
    const proposedText = document.getElementById('mayhenRunnerProposedText');
    const passedText = document.getElementById('mayhenRunnerPassedText');
    const rejectedText = document.getElementById('mayhenRunnerRejectedText');
    const elapsedText = document.getElementById('mayhenRunnerElapsedText');
    const progressTrack = document.getElementById('mayhenRunnerProgressTrack');
    const progressFill = document.getElementById('mayhenRunnerProgressFill');
    const liveMsg = document.getElementById('mayhenRunnerLiveMsg');
    const logList = document.getElementById('mayhenRunnerLogList');

    let currentRun = null;
    let isLoopRunning = false;
    let stopRequested = false;
    let nextStepTimer = null;
    let elapsedTimer = null;
    let loopStartedAtMs = null;
    let networkRetryCount = 0;

    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    };

    const resolveRunUrl = (template, runId) => template.replace('__RUN_ID__', String(runId));

    const formatDuration = (ms) => {
        const totalSec = Math.max(0, Math.floor(ms / 1000));
        const hrs = String(Math.floor(totalSec / 3600)).padStart(2, '0');
        const mins = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
        const secs = String(totalSec % 60).padStart(2, '0');
        return `${hrs}:${mins}:${secs}`;
    };

    const startElapsedClock = () => {
        if (elapsedTimer) {
            return;
        }
        if (!loopStartedAtMs) {
            loopStartedAtMs = Date.now();
        }
        elapsedTimer = window.setInterval(() => {
            if (elapsedText && loopStartedAtMs) {
                elapsedText.textContent = formatDuration(Date.now() - loopStartedAtMs);
            }
        }, 1000);
    };

    const stopElapsedClock = () => {
        if (elapsedTimer) {
            window.clearInterval(elapsedTimer);
            elapsedTimer = null;
        }
    };

    const setBadge = (text, variant = 'ok') => {
        if (!statusBadge) {
            return;
        }
        statusBadge.textContent = text;
        statusBadge.classList.remove('mayhen_admin_ai_tag_ok', 'mayhen_admin_ai_tag_customer', 'mayhen_admin_ai_tag_danger');
        if (variant === 'danger') {
            statusBadge.classList.add('mayhen_admin_ai_tag_danger');
        } else if (variant === 'warn') {
            statusBadge.classList.add('mayhen_admin_ai_tag_customer');
        } else {
            statusBadge.classList.add('mayhen_admin_ai_tag_ok');
        }
    };

    const renderRunSnapshot = (run, statusMessage = '') => {
        if (!run) {
            return;
        }
        currentRun = run;

        if (progressPanel) {
            progressPanel.hidden = false;
        }
        if (idText) {
            idText.textContent = `#${run.id}`;
        }
        if (stepText) {
            stepText.textContent = `${run.completed_steps} / ${run.total_steps}`;
        }
        const pct = Math.max(0, Math.min(100, Number(run.percent || 0)));
        if (percentText) {
            percentText.textContent = `${pct}%`;
        }
        if (progressFill) {
            progressFill.style.width = `${pct}%`;
        }
        if (progressTrack) {
            progressTrack.setAttribute('aria-valuenow', String(Math.round(pct)));
        }
        if (proposedText) {
            proposedText.textContent = String(run.proposed ?? 0);
        }
        if (passedText) {
            passedText.textContent = String(run.passed ?? 0);
        }
        if (rejectedText) {
            rejectedText.textContent = String(run.rejected ?? 0);
        }
        if (liveMsg && statusMessage) {
            liveMsg.textContent = statusMessage;
        }

        if (logList && Array.isArray(run.recent_events)) {
            logList.textContent = '';
            const reversed = [...run.recent_events].reverse();
            reversed.forEach((ev) => {
                const li = document.createElement('li');
                li.className = `mayhen_admin_ai_runner_log_item mayhen_admin_ai_runner_log_${ev.type || 'info'}`;
                const timeSpan = document.createElement('span');
                timeSpan.className = 'mayhen_admin_ai_runner_log_time';
                timeSpan.textContent = `[${ev.time || '--:--:--'}] `;
                const msgSpan = document.createElement('span');
                msgSpan.textContent = ev.text || '';
                li.appendChild(timeSpan);
                li.appendChild(msgSpan);
                logList.appendChild(li);
            });
        }

        const isRunning = run.status === 'running';
        const isPaused = run.status === 'paused';
        const isDone = run.status === 'completed' || run.status === 'cancelled' || run.status === 'failed';

        if (startBtn) {
            startBtn.hidden = isRunning || isPaused;
        }
        if (pauseBtn) {
            pauseBtn.hidden = !isRunning;
        }
        if (resumeBtn) {
            resumeBtn.hidden = !isPaused;
        }
        if (cancelBtn) {
            cancelBtn.hidden = !(isRunning || isPaused);
        }
        if (refreshBtn) {
            refreshBtn.hidden = !isDone && Number(run.passed || 0) === 0;
        }

        if (isRunning) {
            const phaseLabel = run.current_phase === 'verify' ? 'Critic AI phản biện' : 'Proposal AI đề xuất';
            setBadge(`Đang chạy • Vòng ${run.current_round}/${run.rounds} (${phaseLabel})`, 'warn');
        } else if (isPaused) {
            setBadge('Đang tạm dừng', 'warn');
        } else if (run.status === 'completed') {
            setBadge('Hoàn tất', 'ok');
        } else {
            setBadge('Đã dừng', 'danger');
        }
    };

    const scheduleNextStep = (delayMs) => {
        if (nextStepTimer) {
            window.clearTimeout(nextStepTimer);
            nextStepTimer = null;
        }
        if (stopRequested || !currentRun || currentRun.status !== 'running') {
            isLoopRunning = false;
            stopElapsedClock();
            return;
        }
        nextStepTimer = window.setTimeout(executeStep, Math.max(1200, Number(delayMs || 3000)));
    };

    const executeStep = async () => {
        if (stopRequested || !currentRun || currentRun.status !== 'running') {
            isLoopRunning = false;
            stopElapsedClock();
            return;
        }

        isLoopRunning = true;
        startElapsedClock();

        const phaseText = currentRun.current_phase === 'verify'
            ? 'Đang gọi Critic Reviewer AI + Hard-Guard...'
            : `Đang gọi Proposal AI cho chủ đề [${currentRun.current_topic || '...'}]...`;
        if (liveMsg) {
            liveMsg.textContent = `Vòng ${currentRun.current_round}/${currentRun.rounds} — ${phaseText}`;
        }

        try {
            const res = await fetch(resolveRunUrl(stepTpl, currentRun.id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({}),
            });

            const json = await res.json().catch(() => null);
            if (!res.ok || !json) {
                throw new Error(`HTTP ${res.status}`);
            }

            networkRetryCount = 0;

            if (json.run) {
                renderRunSnapshot(json.run, json.message || '');
            }

            if (json.done || !json.run || json.run.status !== 'running') {
                isLoopRunning = false;
                stopElapsedClock();
                return;
            }

            const delay = Number(json.retry_after_ms || json.run.cooldown_ms || 3500);
            if (liveMsg) {
                const restSec = (delay / 1000).toFixed(1);
                liveMsg.textContent = `${json.message || 'Xong 1 vi-bước.'} Nghỉ ${restSec}s nhường tải server trước bước tiếp...`;
            }
            scheduleNextStep(delay);
        } catch (err) {
            networkRetryCount += 1;
            const backoffMs = Math.min(20000, 4000 * networkRetryCount);
            if (liveMsg) {
                liveMsg.textContent = `Mạng hoặc server bận tạm thời (${err.message}). Tự động thử lại bước hiện tại sau ${(backoffMs / 1000).toFixed(0)}s (lần ${networkRetryCount})...`;
            }
            if (networkRetryCount <= 8) {
                scheduleNextStep(backoffMs);
            } else {
                isLoopRunning = false;
                stopElapsedClock();
                setBadge('Mất kết nối — Bấm Tiếp tục để chạy tiếp', 'danger');
                if (resumeBtn) {
                    resumeBtn.hidden = false;
                }
            }
        }
    };

    startBtn?.addEventListener('click', async () => {
        const selectedTopics = Array.from(container.querySelectorAll('input[name="runner_topics"]:checked'))
            .map((el) => el.value);

        if (selectedTopics.length === 0) {
            if (liveMsg) {
                liveMsg.textContent = 'Vui lòng chọn ít nhất 1 chủ đề để huấn luyện.';
            }
            return;
        }

        startBtn.disabled = true;
        stopRequested = false;
        networkRetryCount = 0;
        loopStartedAtMs = Date.now();

        try {
            const res = await fetch(startUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    topics: selectedTopics,
                    per_topic: Number(perTopicEl?.value || 2),
                    rounds: Number(roundsEl?.value || 3),
                    cooldown_ms: Number(cooldownEl?.value || 3500),
                }),
            });

            const json = await res.json().catch(() => null);
            if (!json) {
                throw new Error(`HTTP ${res.status}`);
            }

            if (json.run) {
                renderRunSnapshot(json.run, json.message || '');
            }

            if (res.ok && json.ok && json.run && json.run.status === 'running') {
                executeStep();
            } else if (liveMsg) {
                liveMsg.textContent = json.message || 'Không thể khởi tạo phiên chạy mới.';
            }
        } catch (err) {
            if (progressPanel) {
                progressPanel.hidden = false;
            }
            if (liveMsg) {
                liveMsg.textContent = `Lỗi khởi tạo phiên: ${err.message}`;
            }
        } finally {
            startBtn.disabled = false;
        }
    });

    pauseBtn?.addEventListener('click', async () => {
        if (!currentRun) {
            return;
        }
        stopRequested = true;
        if (nextStepTimer) {
            window.clearTimeout(nextStepTimer);
            nextStepTimer = null;
        }
        stopElapsedClock();
        try {
            const res = await fetch(resolveRunUrl(pauseTpl, currentRun.id), {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            });
            const json = await res.json().catch(() => null);
            if (json && json.run) {
                renderRunSnapshot(json.run, 'Đã tạm dừng an toàn. Bạn có thể bấm Tiếp Tục Chạy bất cứ lúc nào.');
            }
        } catch {
            setBadge('Đã tạm dừng trên trình duyệt', 'warn');
        }
    });

    resumeBtn?.addEventListener('click', async () => {
        if (!currentRun) {
            return;
        }
        stopRequested = false;
        networkRetryCount = 0;
        try {
            const res = await fetch(resolveRunUrl(resumeTpl, currentRun.id), {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            });
            const json = await res.json().catch(() => null);
            if (json && json.run) {
                renderRunSnapshot(json.run, 'Đang nối lại phiên huấn luyện...');
                if (!isLoopRunning) {
                    executeStep();
                }
            }
        } catch (err) {
            if (liveMsg) {
                liveMsg.textContent = `Không thể nối lại phiên: ${err.message}`;
            }
        }
    });

    cancelBtn?.addEventListener('click', async () => {
        if (!currentRun || !window.confirm('Dừng hẳn phiên huấn luyện này? Các ứng viên đã vượt qua kiểm định trước đó vẫn được giữ an toàn trong Hàng đợi duyệt.')) {
            return;
        }
        stopRequested = true;
        if (nextStepTimer) {
            window.clearTimeout(nextStepTimer);
            nextStepTimer = null;
        }
        stopElapsedClock();
        try {
            const res = await fetch(resolveRunUrl(cancelTpl, currentRun.id), {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            });
            const json = await res.json().catch(() => null);
            if (json && json.run) {
                renderRunSnapshot(json.run, 'Đã dừng hẳn phiên huấn luyện.');
            }
        } catch {
            setBadge('Đã hủy', 'danger');
        }
    });

    // Tự động khôi phục phiên đang chạy / tạm dừng nếu Admin F5 trang
    const rawActive = container.dataset.activeRun;
    if (rawActive && rawActive !== 'null') {
        try {
            const activeRun = JSON.parse(rawActive);
            if (activeRun && activeRun.id) {
                renderRunSnapshot(
                    activeRun,
                    activeRun.status === 'running'
                        ? 'Đã khôi phục phiên đang chạy từ máy chủ — đang tự động chạy tiếp vi-bước kế tiếp...'
                        : 'Phiên đang tạm dừng. Bấm "Tiếp Tục Chạy" để tiếp tục từ đúng vị trí cũ.'
                );
                if (activeRun.status === 'running') {
                    stopRequested = false;
                    scheduleNextStep(1500);
                }
            }
        } catch {
            // Silent ignore invalid JSON
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initConfirmForms();
    initCleanUiDrawers();
    initChunkedLearningRunner();
});
