using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace PAWSProject.Models
{
    public class tbluserModel
    {
        public int userID { get; set; }
        public string username { get; set; }
        public string password { get; set; }
        public string accessLvl { get; set; }
        public int isActive { get; set; }
        public DateTime createdAt { get; set; }
        public DateTime updateAt { get; set; }
    }
}
