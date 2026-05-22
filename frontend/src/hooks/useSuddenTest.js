import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { AuthContext } from '../context/AuthProvider';
import { suddenTestApi } from '../services/suddenTest';

/**
 * Schedules random sudden-test popups while the learner is on a lesson page.
 * Interval is randomized between server min/max when enabled globally.
 */
export function useSuddenTest(active = true) {
  const { user, refreshUser } = useContext(AuthContext);
  const [config, setConfig] = useState(null);
  const [challenge, setChallenge] = useState(null);
  const [visible, setVisible] = useState(false);
  const [loadingChallenge, setLoadingChallenge] = useState(false);
  const timerRef = useRef(null);

  useEffect(() => {
    suddenTestApi
      .getConfig()
      .then((res) => setConfig(res.data.data))
      .catch(() => setConfig({ enabled: false }));
  }, []);

  const scheduleNext = useCallback(() => {
    if (timerRef.current) clearTimeout(timerRef.current);
    if (!active || !user || !config?.enabled || visible) return;

    const min = (config.min_interval_seconds ?? 180) * 1000;
    const max = (config.max_interval_seconds ?? 480) * 1000;
    const delay = min + Math.random() * (max - min);

    timerRef.current = setTimeout(async () => {
      setLoadingChallenge(true);
      try {
        const res = await suddenTestApi.fetchChallenge();
        setChallenge(res.data.data);
        setVisible(true);
      } catch {
        scheduleNext();
      } finally {
        setLoadingChallenge(false);
      }
    }, delay);
  }, [active, user, config, visible]);

  useEffect(() => {
    scheduleNext();
    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, [scheduleNext]);

  const closeModal = () => {
    setVisible(false);
    setChallenge(null);
    scheduleNext();
  };

  const submitResult = async (payload) => {
    if (!challenge?.question?.id) return null;
    const res = await suddenTestApi.submit(challenge.question.id, payload);
    await refreshUser();
    return res.data.data;
  };

  return {
    config,
    challenge,
    visible,
    loadingChallenge,
    closeModal,
    submitResult,
    timerSeconds:
      challenge?.timer_seconds ??
      challenge?.question?.time_limit_seconds ??
      config?.default_timer_seconds ??
      90,
    difficulty: challenge?.difficulty ?? 2,
  };
}
