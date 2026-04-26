import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'runId',
        'canvas',
        'generation',
        'frameIndex',
        'fitness',
        'averageFitness',
        'seed',
        'mode',
        'dataset',
        'program',
        'status',
        'playback',
        'speed',
        'speedLabel',
        'grid',
    ];
    static values = { defaultRun: String };

    connect() {
        this.eventSource = null;
        this.buffer = [];
        this.currentFrame = null;
        this.playing = true;
        this.timer = null;
        this.frameCount = 0;
        this.speedMs = 250;
        this.streamCompleted = false;
        if (this.hasDefaultRunValue && this.defaultRunValue !== '') {
            this.runIdTarget.value = this.defaultRunValue;
        }
        this.ctx = this.canvasTarget.getContext('2d');
        this.reset();
    }

    start() {
        this.stop();
        this.clearPlayback();
        this.streamCompleted = false;
        const runId = (this.runIdTarget.value || '').trim();
        if (runId === '') {
            this.setStatus('Missing run id');
            return;
        }

        this.playing = true;
        this.updatePlaybackLabel();
        const url = `/runs/${encodeURIComponent(runId)}/stream`;
        this.eventSource = new EventSource(url);

        this.eventSource.addEventListener('chunk', (event) => {
            const payload = JSON.parse(event.data);
            this.buffer.push(payload);
            this.schedulePlayback();
        });

        this.eventSource.addEventListener('done', () => {
            this.streamCompleted = true;
            this.closeEventSource();
            if (this.buffer.length === 0 && !this.timer) {
                this.setStatus(`Stream completed (${this.frameCount} frames)`);
            }
        });

        this.eventSource.addEventListener('error', (event) => {
            const payload = event?.data ? JSON.parse(event.data) : null;
            this.setStatus(payload?.message || 'Stream error');
        });

        this.setStatus(`Streaming ${runId}`);
    }

    stop() {
        this.closeEventSource();
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
    }

    closeEventSource() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }

    reset() {
        this.stop();
        this.clearPlayback();
        const side = this.canvasTarget.width;
        this.ctx.fillStyle = '#000000';
        this.ctx.fillRect(0, 0, side, side);
        this.generationTarget.textContent = '-';
        this.frameIndexTarget.textContent = '-';
        this.fitnessTarget.textContent = '-';
        this.averageFitnessTarget.textContent = '-';
        this.seedTarget.textContent = '-';
        this.modeTarget.textContent = '-';
        this.datasetTarget.textContent = '-';
        this.programTarget.textContent = '-';
        this.updateSpeed();
        this.updatePlaybackLabel();
        this.setStatus('Idle');
    }

    togglePlayback() {
        this.playing = !this.playing;
        this.updatePlaybackLabel();
        this.schedulePlayback();
    }

    updateSpeed() {
        this.speedMs = Number.parseInt(this.speedTarget?.value || '250', 10);
        this.speedLabelTarget.textContent = `${this.speedMs}`;
    }

    redrawCurrentFrame() {
        if (this.currentFrame) {
            this.drawFrame(this.currentFrame);
        }
    }

    schedulePlayback() {
        if (!this.playing || this.timer || this.buffer.length === 0) {
            return;
        }

        this.timer = setTimeout(() => {
            this.timer = null;
            const frame = this.buffer.shift();
            if (frame) {
                this.renderFrame(frame);
            }
            if (this.buffer.length === 0 && this.streamCompleted) {
                this.setStatus(`Stream completed (${this.frameCount} frames)`);
                return;
            }
            this.schedulePlayback();
        }, this.speedMs);
    }

    clearPlayback() {
        this.buffer = [];
        this.currentFrame = null;
        this.frameCount = 0;
        this.streamCompleted = false;
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
    }

    renderFrame(frame) {
        this.currentFrame = frame;
        this.frameCount += 1;
        this.drawFrame(frame);

        const metadata = frame.metadata || {};
        this.generationTarget.textContent = `${metadata.generation ?? frame.frame ?? '-'}`;
        this.frameIndexTarget.textContent = `${frame.frame ?? this.frameCount}`;
        this.fitnessTarget.textContent = `${metadata.fitness ?? '-'}`;
        this.averageFitnessTarget.textContent = `${metadata.average_fitness ?? '-'}`;
        this.seedTarget.textContent = `${metadata.seed ?? '-'}`;
        this.modeTarget.textContent = `${metadata.mode ?? '-'}`;
        this.datasetTarget.textContent = `${metadata.dataset ?? '-'}`;
        this.programTarget.textContent = metadata.best_program
            ? JSON.stringify(metadata.best_program, null, 2)
            : '-';
        this.setStatus(`Frame ${frame.frame ?? this.frameCount} (${this.buffer.length} buffered)`);
    }

    drawFrame(frame) {
        const cells = frame.cells || [];
        const size = frame.size || cells.length || 1;
        const pixel = this.canvasTarget.width / size;

        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                const value = cells[y]?.[x] || 0;
                this.ctx.fillStyle = value ? '#ffffff' : '#000000';
                this.ctx.fillRect(x * pixel, y * pixel, pixel, pixel);
            }
        }

        if (this.gridTarget.checked && size <= 64) {
            this.ctx.strokeStyle = 'rgba(148, 163, 184, 0.28)';
            this.ctx.lineWidth = 1;
            for (let index = 0; index <= size; index += 1) {
                const position = Math.round(index * pixel) + 0.5;
                this.ctx.beginPath();
                this.ctx.moveTo(position, 0);
                this.ctx.lineTo(position, this.canvasTarget.height);
                this.ctx.stroke();
                this.ctx.beginPath();
                this.ctx.moveTo(0, position);
                this.ctx.lineTo(this.canvasTarget.width, position);
                this.ctx.stroke();
            }
        }
    }

    updatePlaybackLabel() {
        this.playbackTarget.textContent = this.playing ? 'Pause' : 'Play';
    }

    setStatus(message) {
        this.statusTarget.textContent = message;
    }
}
