const { useState, useEffect, useMemo } = React;

// --- Icons (Heroicons inline SVGs) ---
const Icons = {
  Upload: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>,
  Download: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l-4 4m0 0l-4-4m4 4V4" /></svg>,
  Edit: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>,
  Delete: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>,
  Login: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>,
  Register: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>,
  System: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>,
  Warning: () => <svg className="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>,
  Search: () => <svg className="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>,
  Close: () => <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>,
  Notes: () => <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>,
  Users: () => <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>,
  ShieldCheck: () => <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
};

// --- Helper Functions ---
const timeAgo = (dateStr) => {
    const date = new Date(dateStr);
    const seconds = Math.floor((new Date() - date) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " years ago";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " months ago";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " days ago";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " hours ago";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " mins ago";
    return "Just now";
};

const getActionInfo = (action) => {
    const act = action.toUpperCase();
    if (act.includes('UPLOAD')) return { icon: <Icons.Upload />, color: 'bg-green-100 text-green-700', border: 'border-green-200' };
    if (act.includes('DELETE')) return { icon: <Icons.Delete />, color: 'bg-red-100 text-red-700', border: 'border-red-200' };
    if (act.includes('EDIT')) return { icon: <Icons.Edit />, color: 'bg-blue-100 text-blue-700', border: 'border-blue-200' };
    if (act.includes('DOWNLOAD')) return { icon: <Icons.Download />, color: 'bg-purple-100 text-purple-700', border: 'border-purple-200' };
    if (act.includes('LOGIN')) return { icon: <Icons.Login />, color: 'bg-emerald-100 text-emerald-700', border: 'border-emerald-200' };
    if (act.includes('REGISTER')) return { icon: <Icons.Register />, color: 'bg-indigo-100 text-indigo-700', border: 'border-indigo-200' };
    return { icon: <Icons.System />, color: 'bg-gray-100 text-gray-700', border: 'border-gray-200' };
};

const isSuspiciousLog = (log) => {
    const details = (log.details || '').toLowerCase();
    if (details.includes('failed') || details.includes('invalid') || details.includes('suspended')) return true;
    if (log.action === 'DELETE' && details.includes('multiple')) return true; // heuristics
    return false;
};

// --- Components ---

const StatsRow = ({ stats }) => (
    <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        {[
            { label: 'Uploads Today', value: stats.uploads_today, icon: <Icons.Upload />, color: 'text-green-600', bg: 'bg-green-50' },
            { label: 'New Students', value: stats.new_students, icon: <Icons.Register />, color: 'text-indigo-600', bg: 'bg-indigo-50' },
            { label: 'Downloads Today', value: stats.downloads_today, icon: <Icons.Download />, color: 'text-purple-600', bg: 'bg-purple-50' },
            { label: 'Deleted Notes', value: stats.deleted_notes, icon: <Icons.Delete />, color: 'text-red-600', bg: 'bg-red-50' },
            { label: 'Active Students', value: stats.active_students, icon: <Icons.Users />, color: 'text-blue-600', bg: 'bg-blue-50' },
        ].map((stat, i) => (
            <div key={i} className="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center gap-4 transition-transform hover:scale-105">
                <div className={`p-3 rounded-lg ${stat.bg} ${stat.color}`}>
                    {stat.icon}
                </div>
                <div>
                    <div className="text-2xl font-bold text-slate-800">{stat.value}</div>
                    <div className="text-xs text-slate-500 font-medium uppercase tracking-wider">{stat.label}</div>
                </div>
            </div>
        ))}
    </div>
);

const ActivityModal = ({ log, onClose }) => {
    if (!log) return null;
    const info = getActionInfo(log.action);
    const suspicious = isSuspiciousLog(log);
    
    return (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-in fade-in duration-200">
            <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                <div className={`p-6 border-b flex items-start justify-between ${info.bg}`}>
                    <div className="flex items-center gap-4">
                        <div className={`p-3 rounded-full bg-white shadow-sm ${info.color}`}>
                            {info.icon}
                        </div>
                        <div>
                            <h3 className="text-xl font-bold text-slate-800 capitalize">{log.action.toLowerCase()} Action</h3>
                            <p className="text-sm text-slate-500">{new Date(log.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                    <button onClick={onClose} className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                        <Icons.Close />
                    </button>
                </div>
                
                <div className="p-6 overflow-y-auto">
                    {suspicious && (
                        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                            <Icons.Warning />
                            <div>
                                <h4 className="text-red-800 font-semibold text-sm">Suspicious Activity Detected</h4>
                                <p className="text-red-600 text-xs mt-1">This action triggered our automated security warnings. Please review the details below.</p>
                            </div>
                        </div>
                    )}
                    
                    <div className="space-y-6">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <p className="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Performed By</p>
                                <p className="font-semibold text-slate-800">{log.actor_name || 'Unknown'}</p>
                                <span className="inline-block mt-1 px-2 py-0.5 bg-slate-200 text-slate-600 text-[10px] font-bold rounded uppercase">
                                    {log.user_type}
                                </span>
                            </div>
                            <div className="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <p className="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Target ID</p>
                                <p className="font-semibold text-slate-800">{log.target_id || 'N/A'}</p>
                                <span className="inline-block mt-1 px-2 py-0.5 bg-slate-200 text-slate-600 text-[10px] font-bold rounded uppercase">
                                    System Ref
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <p className="text-xs text-slate-400 font-medium uppercase tracking-wider mb-2">Details & Changes</p>
                            <div className="bg-slate-50 p-4 rounded-xl border border-slate-100 text-slate-700 text-sm font-mono whitespace-pre-wrap">
                                {log.details || 'No additional details provided for this action.'}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div className="p-4 border-t bg-slate-50 flex justify-end gap-3">
                    {log.user_type === 'student' && (
                        <a href={`manage_students.php?search=${log.target_id}`} className="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                            View Profile
                        </a>
                    )}
                    {(log.action === 'UPLOAD' || log.action === 'EDIT') && (
                        <a href={`manage_notes.php`} className="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                            Open Note
                        </a>
                    )}
                    <button onClick={onClose} className="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 transition-colors shadow-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    );
};

const ActivityLogApp = () => {
    const logs = window.INITIAL_LOGS || [];
    const stats = window.INITIAL_STATS || { uploads_today: 0, new_students: 0, downloads_today: 0, deleted_notes: 0, active_students: 0 };
    
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('All');
    const [actionFilter, setActionFilter] = useState('All');
    const [dateFilter, setDateFilter] = useState('All');
    const [sortOrder, setSortOrder] = useState('Newest');
    const [selectedLog, setSelectedLog] = useState(null);

    const filteredLogs = useMemo(() => {
        let result = logs;
        
        // Search filter
        if (search) {
            const q = search.toLowerCase();
            result = result.filter(l => 
                (l.actor_name || '').toLowerCase().includes(q) || 
                (l.details || '').toLowerCase().includes(q) ||
                (l.action || '').toLowerCase().includes(q)
            );
        }
        
        // Category filter
        if (category !== 'All') {
            if (category === 'Notes') result = result.filter(l => l.details.toLowerCase().includes('note'));
            else if (category === 'Students') result = result.filter(l => l.user_type === 'student');
            else if (category === 'Admin Actions') result = result.filter(l => l.user_type === 'admin');
            else if (category === 'System Logs') result = result.filter(l => !l.user_type || l.user_type === 'system');
        }
        
        // Action filter
        if (actionFilter !== 'All') {
            result = result.filter(l => l.action.toUpperCase() === actionFilter.toUpperCase());
        }
        
        // Date filter
        const today = new Date();
        today.setHours(0,0,0,0);
        const yesterday = new Date(today); yesterday.setDate(yesterday.getDate() - 1);
        const sevenDaysAgo = new Date(today); sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        const thisMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        if (dateFilter !== 'All') {
            result = result.filter(l => {
                const logDate = new Date(l.created_at);
                if (dateFilter === 'Today') return logDate >= today;
                if (dateFilter === 'Yesterday') return logDate >= yesterday && logDate < today;
                if (dateFilter === 'Last 7 Days') return logDate >= sevenDaysAgo;
                if (dateFilter === 'This Month') return logDate >= thisMonth;
                return true;
            });
        }
        
        // Sort
        if (sortOrder === 'Newest') {
            result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        } else if (sortOrder === 'Oldest') {
            result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        }
        
        return result;
    }, [logs, search, category, actionFilter, dateFilter, sortOrder]);

    const categories = ['All', 'Notes', 'Students', 'Admin Actions', 'System Logs'];
    const actionsList = ['All', 'Upload', 'Edit', 'Delete', 'Download', 'Login', 'Register'];
    const dates = ['All', 'Today', 'Yesterday', 'Last 7 Days', 'This Month'];

    return (
        <div className="h-full flex flex-col p-6">
            <div className="mb-6 flex justify-between items-end">
                <div>
                    <h1 className="text-3xl font-extrabold text-slate-800 tracking-tight">Activity Log</h1>
                    <p className="text-slate-500 mt-1">Real-time monitoring of all system activities</p>
                </div>
            </div>

            <StatsRow stats={stats} />

            <div className="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col flex-grow overflow-hidden">
                {/* Filter Bar */}
                <div className="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-4 items-center">
                    <div className="relative flex-grow max-w-md">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Icons.Search />
                        </div>
                        <input
                            type="text"
                            className="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-shadow shadow-sm"
                            placeholder="Search logs by name, keyword, or action..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>
                    
                    <select value={category} onChange={e => setCategory(e.target.value)} className="py-2 px-3 border border-slate-200 bg-white rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-brand-500 outline-none shadow-sm cursor-pointer">
                        {categories.map(c => <option key={c} value={c}>{c}</option>)}
                    </select>

                    <select value={dateFilter} onChange={e => setDateFilter(e.target.value)} className="py-2 px-3 border border-slate-200 bg-white rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-brand-500 outline-none shadow-sm cursor-pointer">
                        {dates.map(d => <option key={d} value={d}>{d}</option>)}
                    </select>
                    
                    <select value={sortOrder} onChange={e => setSortOrder(e.target.value)} className="py-2 px-3 border border-slate-200 bg-white rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-brand-500 outline-none shadow-sm cursor-pointer ml-auto">
                        <option value="Newest">Newest First</option>
                        <option value="Oldest">Oldest First</option>
                    </select>
                </div>
                
                {/* Action Pills */}
                <div className="px-4 py-3 border-b border-slate-100 bg-white flex gap-2 overflow-x-auto">
                    {actionsList.map(act => (
                        <button
                            key={act}
                            onClick={() => setActionFilter(act)}
                            className={`px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors border ${
                                actionFilter === act 
                                ? 'bg-brand-600 text-white border-brand-600 shadow-sm' 
                                : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
                            }`}
                        >
                            {act}
                        </button>
                    ))}
                </div>

                {/* Feed */}
                <div className="flex-grow overflow-y-auto p-4 feed-container bg-slate-50">
                    {filteredLogs.length === 0 ? (
                        <div className="h-full flex flex-col items-center justify-center text-slate-400">
                            <div className="w-16 h-16 mb-4 opacity-50"><Icons.Search /></div>
                            <p className="text-lg font-medium">No activities found</p>
                            <p className="text-sm">Try adjusting your filters</p>
                        </div>
                    ) : (
                        <div className="space-y-3 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                            {filteredLogs.map(log => {
                                const info = getActionInfo(log.action);
                                const isSuspicious = isSuspiciousLog(log);
                                return (
                                    <div 
                                        key={log.id || Math.random()} 
                                        onClick={() => setSelectedLog(log)}
                                        className="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active cursor-pointer"
                                    >
                                        <div className="flex items-center justify-center w-10 h-10 rounded-full border-4 border-slate-50 bg-white shadow-sm shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 relative z-10 transition-transform group-hover:scale-110">
                                            <div className={`w-full h-full rounded-full flex items-center justify-center ${info.color}`}>
                                                {info.icon}
                                            </div>
                                        </div>
                                        
                                        <div className="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-brand-300 group-hover:-translate-y-1">
                                            <div className="flex items-start justify-between mb-1">
                                                <div className="flex items-center gap-2">
                                                    <span className={`px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border ${info.border} ${info.color}`}>
                                                        {log.action}
                                                    </span>
                                                    {isSuspicious && (
                                                        <span title="Suspicious Activity" className="flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-600 animate-pulse">
                                                            <Icons.Warning />
                                                        </span>
                                                    )}
                                                </div>
                                                <time className="text-xs font-medium text-slate-400 flex items-center gap-1">
                                                    {timeAgo(log.created_at)}
                                                </time>
                                            </div>
                                            <h4 className="text-sm font-semibold text-slate-800 mb-1 leading-snug truncate">
                                                {log.actor_name || 'System'} {log.action.toLowerCase()}d an item
                                            </h4>
                                            <p className="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                                {log.details || 'No details provided.'}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
            
            <ActivityModal log={selectedLog} onClose={() => setSelectedLog(null)} />
        </div>
    );
};

const root = ReactDOM.createRoot(document.getElementById('react-activity-root'));
root.render(<ActivityLogApp />);
