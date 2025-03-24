const UserAvatar = ({ user, online = null, profile = false }) => {
    let onlineClass = online === true ? "online" : online === false ? "offline" : "";
    const sizeClass = profile ? "w-40 h-40" : "w-8 h-8";
  
    return (
      <div className={`relative flex items-center ${onlineClass}`}>
        {user.avatar_url ? (
          <div className={`avatar ${onlineClass}`}>
            <img src={user.avatar_url} className={`rounded-full ${sizeClass}`} alt="User Avatar" />
          </div>
        ) : (
          <div className={`avatar placeholder ${onlineClass}`}>
            <div className={`bg-gray-400 text-gray-800 rounded-full ${sizeClass} flex items-center justify-center`}> 
              <span className="text-xl">{user.name?.substring(0, 1)}</span>
            </div>
          </div>
        )}
  
        {online && (
          <span className="absolute top-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full" title="Active Now"></span>
        )}
        {online === false && (
          <span className="absolute top-0 right-0 w-3 h-3 bg-gray-400 border-2 border-white rounded-full" title="Offline"></span>
        )}
      </div>
    );
  };
  
  export default UserAvatar;
  