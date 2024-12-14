using MySql.Data.EntityFramework;
using System;
using System.Collections.Generic;
using System.Data.Entity;
using System.Linq;
using System.Web;

namespace PAWSProject.Models
{
    [DbConfigurationType(typeof(MySqlEFConfiguration))]
    public class PawsContext : DbContext
    {

        static PawsContext()
        {
            Database.SetInitializer<PawsContext>(null);
        }

        public PawsContext() : base("Name=pawsdb")
        {

        }

        public virtual DbSet<tblpetModel> tblpet { get; set; }
        public virtual DbSet<tbluserModel> tbluser { get; set; }



        protected override void OnModelCreating(DbModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);
            modelBuilder.Configurations.Add(new tblpetMap());
            modelBuilder.Configurations.Add(new tbluserMap());
        }
    }
}